<?php

declare(strict_types=1);

use Pdrs\Config\AppConfig;
use Pdrs\Http\Request;
use Pdrs\Http\Response;
use Pdrs\Http\Router;
use Pdrs\Middleware\OperationsGuard;
use Pdrs\Middleware\SecurityHeaders;
use Pdrs\Middleware\SessionSecurity;
use Pdrs\Repository\AuditRepository;
use Pdrs\Repository\EventRepository;
use Pdrs\Repository\MetricsRepository;
use Pdrs\Repository\RateLimitRepository;
use Pdrs\Repository\RegistrationRepository;
use Pdrs\Repository\VerificationRepository;
use Pdrs\Service\ApprovalService;
use Pdrs\Service\AuditLogger;
use Pdrs\Service\CryptoService;
use Pdrs\Service\CsrfService;
use Pdrs\Service\FieldMapper;
use Pdrs\Service\IcsService;
use Pdrs\Service\MoodleClient;
use Pdrs\Service\NotificationService;
use Pdrs\Service\ProvisioningService;
use Pdrs\Service\RateLimiter;
use Pdrs\Service\ReadinessService;
use Pdrs\Service\RegistrationService;
use Pdrs\Service\VerificationService;
use Pdrs\Support\Database;
use Pdrs\Support\Validator;
use Pdrs\Support\View;

require __DIR__ . '/../src/bootstrap.php';

SessionSecurity::start();
SecurityHeaders::apply();

$db = Database::connection();
$crypto = new CryptoService(AppConfig::appKey());
$events = new EventRepository($db);
$registrationRepository = new RegistrationRepository($db);
$notificationService = new NotificationService();
$csrf = new CsrfService();
$verifications = new VerificationService(
    new VerificationRepository($db),
    $crypto,
    $notificationService
);
$provisioning = new ProvisioningService(
    $registrationRepository,
    new FieldMapper(),
    new MoodleClient(),
    $notificationService
);
$registrations = new RegistrationService(
    $registrationRepository,
    $crypto,
    new ApprovalService(),
    $provisioning,
    $notificationService
);
$rateLimiter = new RateLimiter(new RateLimitRepository($db), $crypto);
$audit = new AuditLogger(new AuditRepository($db), $crypto);
$router = new Router();

$router->get('/health', fn (): Response => Response::json(['status' => 'ok', 'service' => 'pdrs']));

$router->get('/readiness', function () use ($db): Response {
    $result = (new ReadinessService($db))->check();

    return Response::json($result, $result['status'] === 'ready' ? 200 : 503);
});

$router->get('/ops/metrics', function (Request $request) use ($db): Response {
    if (!OperationsGuard::authorized($request)) {
        return Response::json(['message' => 'Unauthorized'], 401);
    }

    return Response::json([
        'service' => 'pdrs',
        'generated_at' => gmdate('c'),
        'metrics' => (new MetricsRepository($db))->snapshot(),
    ]);
});

$router->get('/', function (): Response {
    $body = <<<HTML
<section class="hero-card">
  <p class="eyebrow">Professional Development Registration System</p>
  <h1>Secure registration and learning platform enrollment for institutional programs.</h1>
  <p>A controlled registration foundation for professional development events, identity verification, approval workflows, and Moodle provisioning.</p>
  <div class="capability-grid">
    <article><strong>Email verification</strong><span>OTP and signed-link confirmation before records are created.</span></article>
    <article><strong>Approval rules</strong><span>Domain allow-lists, payment checks, and event-specific policies.</span></article>
    <article><strong>Moodle automation</strong><span>User creation, course enrollment, and cohort assignment.</span></article>
    <article><strong>Operational control</strong><span>Audit logs, rate limits, readiness checks, and retry utilities.</span></article>
  </div>
</section>
HTML;

    return new Response(View::render('Professional Development Registration System', $body));
});

$router->get('/e/{slug}', function (Request $request, array $params) use ($events, $csrf): Response {
    $event = $events->findActiveBySlug($params['slug']);
    if (!$event) {
        return Response::json(['message' => 'Event not found'], 404);
    }

    return new Response(View::render($event['title'], renderEventPage($event, $csrf)));
});

$router->post('/e/{slug}/verify', function (Request $request, array $params) use ($events, $rateLimiter, $verifications, $audit, $csrf): Response {
    $event = $events->findActiveBySlug($params['slug']);
    if (!$event) {
        return Response::json(['message' => 'Event not found'], 404);
    }

    if (!$csrf->validate($request)) {
        return Response::json(['message' => 'The registration session has expired. Please refresh and try again.'], 419);
    }

    $email = strtolower(trim((string) $request->input('email')));
    if (!Validator::email($email)) {
        return Response::json(['message' => 'A valid email address is required.'], 422);
    }

    if (!$rateLimiter->allow($request, 'email-verification', $email)) {
        $audit->record('verification.rate_limited', $request, ['entity_type' => 'event', 'entity_id' => $event['id']]);
        return Response::json(['message' => 'Too many verification attempts. Please try again later.'], 429);
    }

    $verifications->issue($event, $email, $request);
    $audit->record('verification.issued', $request, ['entity_type' => 'event', 'entity_id' => $event['id']]);

    return new Response(View::render('Verification sent', renderOtpForm($event, $email, $csrf)));
});

$router->post('/e/{slug}/otp', function (Request $request, array $params) use ($events, $verifications, $audit, $rateLimiter, $csrf): Response {
    $event = $events->findActiveBySlug($params['slug']);
    if (!$event) {
        return Response::json(['message' => 'Event not found'], 404);
    }

    if (!$csrf->validate($request)) {
        return Response::json(['message' => 'The registration session has expired. Please refresh and try again.'], 419);
    }

    $email = strtolower(trim((string) $request->input('email')));
    if (!$rateLimiter->allow($request, 'otp-verification', $email)) {
        $audit->record('otp.rate_limited', $request, ['entity_type' => 'event', 'entity_id' => $event['id']]);
        return Response::json(['message' => 'Too many verification attempts. Please try again later.'], 429);
    }

    $otp = trim((string) $request->input('otp'));
    $challenge = $verifications->verifyOtp((int) $event['id'], $email, $otp);

    if (!$challenge) {
        $audit->record('verification.failed', $request, ['entity_type' => 'event', 'entity_id' => $event['id']]);
        return Response::json(['message' => 'Verification failed.'], 422);
    }

    $audit->record('verification.completed', $request, ['entity_type' => 'verification', 'entity_id' => $challenge['id']]);
    return new Response(View::render('Complete registration', renderRegistrationForm($event, $challenge, $csrf)));
});

$router->get('/verify', function (Request $request) use ($events, $verifications, $audit, $csrf): Response {
    $token = (string) $request->input('token');
    $challenge = $verifications->verifyToken($token);

    if (!$challenge) {
        return Response::json(['message' => 'Verification link is invalid or expired.'], 422);
    }

    $event = $events->findById((int) $challenge['event_id']);
    if (!$event) {
        return Response::json(['message' => 'Event not found'], 404);
    }

    $audit->record('verification.completed', $request, ['entity_type' => 'verification', 'entity_id' => $challenge['id']]);
    return new Response(View::render('Complete registration', renderRegistrationForm($event, $challenge, $csrf)));
});

$router->post('/e/{slug}/register', function (Request $request, array $params) use ($events, $verifications, $registrations, $audit, $rateLimiter, $csrf): Response {
    $event = $events->findActiveBySlug($params['slug']);
    if (!$event) {
        return Response::json(['message' => 'Event not found'], 404);
    }

    if (!$csrf->validate($request)) {
        return Response::json(['message' => 'The registration session has expired. Please refresh and try again.'], 419);
    }

    $errors = registrationErrors($event, $request->post);
    if ($errors !== []) {
        return Response::json(['message' => 'Validation failed', 'errors' => $errors], 422);
    }

    $challenge = $verifications->verifiedChallenge(
        (int) $request->input('verification_id'),
        (string) $request->input('verification_signature')
    );

    if (!$challenge || (int) $challenge['event_id'] !== (int) $event['id']) {
        return Response::json(['message' => 'Verified email session is invalid.'], 422);
    }

    if (!$rateLimiter->allow($request, 'registration-submit', (string) $challenge['email'])) {
        $audit->record('registration.rate_limited', $request, ['entity_type' => 'event', 'entity_id' => $event['id']]);
        return Response::json(['message' => 'Too many registration attempts. Please try again later.'], 429);
    }

    $registration = $registrations->register($event, $challenge, $request->post);
    $audit->record('registration.created', $request, [
        'entity_type' => 'registration',
        'entity_id' => $registration['id'],
        'payload' => ['status' => $registration['status']],
    ]);

    return Response::redirect('/thank-you/' . $registration['uuid']);
});

$router->get('/thank-you/{uuid}', function (Request $request, array $params) use ($db): Response {
    $repository = new RegistrationRepository($db);
    $registration = $repository->findByUuid($params['uuid']);

    if (!$registration) {
        return Response::json(['message' => 'Registration not found'], 404);
    }

    return new Response(View::render('Registration received', renderThankYou($registration)));
});

$router->get('/calendar/{uuid}.ics', function (Request $request, array $params) use ($db): Response {
    $registration = (new RegistrationRepository($db))->findByUuid($params['uuid']);
    if (!$registration) {
        return Response::json(['message' => 'Registration not found'], 404);
    }

    $ics = (new IcsService())->build([
        'slug' => $registration['uuid'],
        'title' => $registration['event_title'],
        'summary' => 'Professional development event registration',
        'start_at' => $registration['start_at'],
        'end_at' => $registration['end_at'],
        'location' => $registration['location'],
    ]);

    return new Response($ics, 200, [
        'Content-Type' => 'text/calendar; charset=utf-8',
        'Content-Disposition' => 'attachment; filename="event.ics"',
    ]);
});

$router->dispatch(Request::capture())->send();

function renderEventPage(array $event, CsrfService $csrf): string
{
    $fields = renderCustomFields($event, false);
    $summary = View::e($event['summary']);
    $title = View::e($event['title']);
    $slug = View::e($event['slug']);
    $startAt = View::e($event['start_at']);
    $location = View::e($event['location']);
    $csrfField = $csrf->field();

    return <<<HTML
<section class="hero-card">
  <p class="eyebrow">Professional Development Registration</p>
  <h1>{$title}</h1>
  <p>{$summary}</p>
  <dl class="event-meta">
    <div><dt>Date</dt><dd>{$startAt}</dd></div>
    <div><dt>Location</dt><dd>{$location}</dd></div>
  </dl>
</section>
<section class="form-card">
  <h2>Start registration</h2>
  <p>Verify your email before submitting personal information. This protects identity quality and prevents duplicate records.</p>
  <form method="post" action="/e/{$slug}/verify">
    {$csrfField}
    <label>Email address <input required type="email" name="email" autocomplete="email"></label>
    {$fields}
    <button type="submit">Send verification</button>
  </form>
</section>
HTML;
}

function renderOtpForm(array $event, string $email, CsrfService $csrf): string
{
    $slug = View::e($event['slug']);
    $email = View::e($email);
    $csrfField = $csrf->field();

    return <<<HTML
<section class="form-card">
  <h1>Check your email</h1>
  <p>A one-time code and signed verification link have been sent to {$email}.</p>
  <form method="post" action="/e/{$slug}/otp">
    {$csrfField}
    <input type="hidden" name="email" value="{$email}">
    <label>One-time code <input required inputmode="numeric" name="otp" pattern="[0-9]{6}" maxlength="6"></label>
    <button type="submit">Verify and continue</button>
  </form>
</section>
HTML;
}

function renderRegistrationForm(array $event, array $challenge, CsrfService $csrf): string
{
    $slug = View::e($event['slug']);
    $fields = renderCustomFields($event, true);
    $verificationId = View::e((string) $challenge['id']);
    $verificationSignature = View::e($challenge['signature']);
    $csrfField = $csrf->field();

    return <<<HTML
<section class="form-card">
  <h1>Complete registration</h1>
  <p>Your email is verified. Complete the remaining registration details for Moodle provisioning.</p>
  <form method="post" action="/e/{$slug}/register">
    {$csrfField}
    <input type="hidden" name="verification_id" value="{$verificationId}">
    <input type="hidden" name="verification_signature" value="{$verificationSignature}">
    <label>First name <input required name="first_name" autocomplete="given-name"></label>
    <label>Last name <input required name="last_name" autocomplete="family-name"></label>
    <label>City <input name="city" autocomplete="address-level2"></label>
    {$fields}
    <button type="submit">Submit registration</button>
  </form>
</section>
HTML;
}

function registrationErrors(array $event, array $payload): array
{
    $errors = Validator::required($payload, ['verification_id', 'verification_signature', 'first_name', 'last_name']);

    foreach (['first_name', 'last_name', 'city'] as $field) {
        if (isset($payload[$field]) && strlen((string) $payload[$field]) > 120) {
            $errors[$field] = 'Please keep this value under 120 characters.';
        }
    }

    foreach ($event['custom_fields'] ?? [] as $field) {
        $name = (string) ($field['name'] ?? '');
        if ($name === '') {
            continue;
        }

        if (!empty($field['required']) && trim((string) ($payload[$name] ?? '')) === '') {
            $errors[$name] = 'This field is required.';
        }

        if (isset($payload[$name]) && strlen((string) $payload[$name]) > 500) {
            $errors[$name] = 'Please keep this value under 500 characters.';
        }
    }

    return $errors;
}

function renderThankYou(array $registration): string
{
    $status = View::e($registration['approval_status']);
    $event = View::e($registration['event_title']);
    $uuid = View::e($registration['uuid']);

    return <<<HTML
<section class="hero-card">
  <p class="eyebrow">Registration received</p>
  <h1>Thank you for registering for {$event}.</h1>
  <p>Status: {$status}. You will receive confirmation and learning platform instructions according to the event approval policy.</p>
  <a class="button-link" href="/calendar/{$uuid}.ics">Download calendar invite</a>
</section>
HTML;
}

function renderCustomFields(array $event, bool $includeInputs): string
{
    if (!$includeInputs) {
        return '';
    }

    $html = '';
    foreach ($event['custom_fields'] ?? [] as $field) {
        $name = View::e((string) ($field['name'] ?? ''));
        $label = View::e((string) ($field['label'] ?? $field['name'] ?? 'Additional field'));
        $required = !empty($field['required']) ? 'required' : '';
        $type = (string) ($field['type'] ?? 'text');

        if ($type === 'textarea') {
            $html .= "<label>{$label} <textarea {$required} name=\"{$name}\" rows=\"4\"></textarea></label>";
            continue;
        }

        if ($type === 'select' && is_array($field['options'] ?? null)) {
            $options = '<option value="">Select an option</option>';
            foreach ($field['options'] as $option) {
                $value = View::e((string) $option);
                $options .= "<option value=\"{$value}\">{$value}</option>";
            }
            $html .= "<label>{$label} <select {$required} name=\"{$name}\">{$options}</select></label>";
            continue;
        }

        $inputType = in_array($type, ['text', 'email', 'tel', 'number', 'date'], true) ? $type : 'text';
        $html .= "<label>{$label} <input {$required} type=\"{$inputType}\" name=\"{$name}\"></label>";
    }

    return $html;
}
