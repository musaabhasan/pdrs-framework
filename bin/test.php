<?php

declare(strict_types=1);

use Pdrs\Config\AppConfig;
use Pdrs\Http\Request;
use Pdrs\Http\Response;
use Pdrs\Http\Router;
use Pdrs\Middleware\OperationsGuard;
use Pdrs\Service\ApprovalService;
use Pdrs\Service\CryptoService;
use Pdrs\Service\CsrfService;
use Pdrs\Service\EventFormService;
use Pdrs\Service\FieldMapper;
use Pdrs\Service\IcsService;
use Pdrs\Service\InviteCodeService;
use Pdrs\Service\ProgramModeService;

require __DIR__ . '/../src/bootstrap.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$tests = [];

function test(string $name, callable $callback): void
{
    global $tests;
    $tests[] = [$name, $callback];
}

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function responseField(Response $response, string $field): mixed
{
    $property = (new ReflectionClass($response))->getProperty($field);
    $property->setAccessible(true);

    return $property->getValue($response);
}

function setEnvForTest(string $key, string $value): void
{
    $_ENV[$key] = $value;
    putenv($key . '=' . $value);
}

test('CryptoService encrypts, decrypts, hashes, and signs consistently', function (): void {
    $crypto = new CryptoService(random_bytes(32));
    $cipher = $crypto->encrypt('person@example.edu');

    assertTrue($cipher !== 'person@example.edu', 'Ciphertext must not equal plaintext.');
    assertTrue($crypto->decrypt($cipher) === 'person@example.edu', 'Ciphertext should decrypt to the original value.');
    assertTrue($crypto->hash('User@Example.edu') === $crypto->hash(' user@example.edu '), 'Hashes should normalize lookup values.');
    assertTrue(strlen($crypto->sign('payload')) === 64, 'HMAC signatures should be SHA-256 hex strings.');
});

test('ProgramModeService renders flexible delivery labels', function (): void {
    $modes = new ProgramModeService();
    $labels = $modes->labels([
        'program_modes' => ['synchronous', 'asynchronous', 'self-paced', 'Instructor Led', 'custom_lab'],
    ]);

    assertTrue($labels === ['Synchronous', 'Asynchronous', 'Self-paced', 'Instructor-led', 'Custom Lab'], 'Program modes should normalize known and custom delivery labels.');
    assertTrue($modes->summary(['program_modes' => []]) === 'Flexible delivery', 'Empty modes should render a useful fallback.');
});

test('InviteCodeService enforces optional invite gates', function (): void {
    $crypto = new CryptoService(random_bytes(32));
    $inviteCodes = new InviteCodeService($crypto);
    $hash = $crypto->hash($inviteCodes->normalize('PD-2026'));

    assertTrue($inviteCodes->valid(['invite_code_enabled' => 0], ''), 'Disabled invite gates should not block registration.');
    assertTrue($inviteCodes->valid(['invite_code_enabled' => 1, 'invite_code_hash' => $hash], ' pd -2026 '), 'Invite codes should be normalized before comparison.');
    assertTrue(!$inviteCodes->valid(['invite_code_enabled' => 1, 'invite_code_hash' => $hash], 'wrong'), 'Invalid invite codes should be rejected.');
    assertTrue(!$inviteCodes->valid(['invite_code_enabled' => 1, 'invite_code_hash' => ''], 'PD-2026'), 'Enabled invite gates require a configured hash.');
});

test('EventFormService validates custom field types and options', function (): void {
    $forms = new EventFormService();
    $event = [
        'custom_fields' => [
            ['name' => 'organization', 'label' => 'Organization', 'type' => 'text', 'required' => true],
            ['name' => 'registration_type', 'label' => 'Registration type', 'type' => 'select', 'options' => ['Faculty', 'Leader'], 'required' => true],
            ['name' => 'contact_email', 'label' => 'Contact email', 'type' => 'email'],
            ['name' => 'seats', 'label' => 'Seats', 'type' => 'number'],
            ['name' => 'preferred_date', 'label' => 'Preferred date', 'type' => 'date'],
            ['name' => 'bad-name', 'label' => '<script>', 'type' => 'text'],
        ],
    ];

    $errors = $forms->registrationErrors($event, [
        'verification_id' => '1',
        'verification_signature' => 'sig',
        'first_name' => 'Musaab',
        'last_name' => 'Hasan',
        'organization' => '',
        'registration_type' => 'Invalid',
        'contact_email' => 'not-email',
        'seats' => 'ten',
        'preferred_date' => '2026-02-31',
    ]);

    assertTrue(isset($errors['organization']), 'Required custom fields should be enforced.');
    assertTrue(isset($errors['registration_type']), 'Select values should be validated against configured options.');
    assertTrue(isset($errors['contact_email']), 'Custom email fields should validate email syntax.');
    assertTrue(isset($errors['seats']), 'Custom number fields should validate numeric input.');
    assertTrue(isset($errors['preferred_date']), 'Custom date fields should validate calendar dates.');

    $html = $forms->renderCustomFields($event, true);
    assertTrue(str_contains($html, '<select'), 'Select fields should render as select controls.');
    assertTrue(str_contains($html, 'Faculty'), 'Select options should render.');
    assertTrue(!str_contains($html, '<script>'), 'Field labels should be escaped.');
    assertTrue(!str_contains($html, 'bad-name'), 'Invalid field names should not render.');
});

test('ApprovalService applies payment, domain, and manual approval policies', function (): void {
    $approval = new ApprovalService();
    $event = ['requires_payment' => 0, 'allowed_domains' => ['example.edu'], 'instant_approval' => 1];

    assertTrue($approval->evaluate($event, 'person@example.edu')['status'] === 'approved', 'Allowed domains should be approved.');
    assertTrue($approval->evaluate($event, 'person@other.edu')['status'] === 'pending', 'Unlisted domains should require review.');
    assertTrue($approval->evaluate(array_merge($event, ['requires_payment' => 1]), 'person@example.edu')['status'] === 'pending', 'Missing payment should require review.');
    assertTrue($approval->evaluate(['requires_payment' => 0, 'allowed_domains' => [], 'instant_approval' => 0], 'person@example.edu')['status'] === 'pending', 'Manual approval should be respected.');
});

test('FieldMapper creates Moodle-compatible users and skips invalid custom mappings', function (): void {
    $mapper = new FieldMapper();
    [$user, $password] = $mapper->moodleUser(
        [
            'custom_fields' => [
                ['name' => 'organization', 'moodle_shortname' => 'organization'],
                ['name' => 'bad-name', 'moodle_shortname' => 'bad'],
            ],
        ],
        [
            'email' => 'Person+Test@example.edu',
            'first_name' => 'Person',
            'last_name' => 'Example',
            'city' => 'Online',
            'metadata' => ['organization' => 'College', 'bad-name' => 'Ignored'],
        ]
    );

    assertTrue($user['username'] === 'person.test', 'Usernames should be normalized from the email local part.');
    assertTrue(strlen($password) >= 28, 'Temporary passwords should have enough entropy.');
    assertTrue(count($user['customfields']) === 1, 'Invalid custom field mappings should be skipped.');
});

test('Request uses proxy headers only when explicitly trusted', function (): void {
    setEnvForTest('TRUST_PROXY_HEADERS', 'false');
    $request = new Request('GET', '/', [], [], [
        'REMOTE_ADDR' => '10.0.0.5',
        'HTTP_X_FORWARDED_FOR' => '203.0.113.10, 10.0.0.5',
    ]);
    assertTrue($request->ip() === '10.0.0.5', 'Untrusted proxy headers should be ignored.');

    setEnvForTest('TRUST_PROXY_HEADERS', 'true');
    assertTrue($request->ip() === '203.0.113.10', 'Trusted proxy headers should use the first forwarded IP.');
});

test('CsrfService accepts valid tokens and rejects invalid tokens', function (): void {
    $csrf = new CsrfService();
    $token = $csrf->token();
    $valid = new Request('POST', '/', [], ['_csrf_token' => $token], []);
    $invalid = new Request('POST', '/', [], ['_csrf_token' => 'bad'], []);

    assertTrue($csrf->validate($valid), 'Valid CSRF tokens should pass.');
    assertTrue(!$csrf->validate($invalid), 'Invalid CSRF tokens should fail.');
});

test('Router dispatches parameterized routes and returns 404 for misses', function (): void {
    $router = new Router();
    $router->get('/e/{slug}', fn (Request $request, array $params): Response => Response::json(['slug' => $params['slug']]));

    $matched = $router->dispatch(new Request('GET', '/e/demo-event', [], [], []));
    $missing = $router->dispatch(new Request('GET', '/missing', [], [], []));

    assertTrue(json_decode(responseField($matched, 'body'), true)['slug'] === 'demo-event', 'Parameterized routes should pass named params.');
    assertTrue(responseField($missing, 'status') === 404, 'Missing routes should return 404.');
});

test('OperationsGuard requires configured bearer token hash', function (): void {
    setEnvForTest('OPERATIONS_TOKEN_HASH', hash('sha256', 'ops-token'));
    $valid = new Request('GET', '/ops/metrics', [], [], ['HTTP_AUTHORIZATION' => 'Bearer ops-token']);
    $invalid = new Request('GET', '/ops/metrics', [], [], ['HTTP_AUTHORIZATION' => 'Bearer wrong']);

    assertTrue(OperationsGuard::authorized($valid), 'Valid operations token should be authorized.');
    assertTrue(!OperationsGuard::authorized($invalid), 'Invalid operations token should be rejected.');
});

test('IcsService builds escaped calendar invites', function (): void {
    setEnvForTest('APP_URL', 'https://registration.example.edu');
    $ics = (new IcsService())->build([
        'slug' => 'demo',
        'title' => 'Governance, Security',
        'summary' => 'Line one; line two',
        'start_at' => '2026-06-10 09:00:00',
        'end_at' => '2026-06-10 10:00:00',
        'location' => 'Online',
    ]);

    assertTrue(str_contains($ics, 'BEGIN:VCALENDAR'), 'Calendar invite should contain VCALENDAR.');
    assertTrue(str_contains($ics, 'SUMMARY:Governance\, Security'), 'Calendar fields should escape commas.');
    assertTrue(str_contains($ics, 'DESCRIPTION:Line one\; line two'), 'Calendar fields should escape semicolons.');
});

$failed = 0;
foreach ($tests as [$name, $callback]) {
    try {
        $callback();
        echo '[PASS] ' . $name . PHP_EOL;
    } catch (Throwable $exception) {
        $failed++;
        fwrite(STDERR, '[FAIL] ' . $name . ': ' . $exception->getMessage() . PHP_EOL);
    }
}

if ($failed > 0) {
    exit(1);
}

echo 'test-suite-ok' . PHP_EOL;
