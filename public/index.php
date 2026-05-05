<?php

declare(strict_types=1);

use Pdrs\Config\AppConfig;
use Pdrs\Http\Request;
use Pdrs\Http\Response;
use Pdrs\Http\Router;
use Pdrs\Middleware\SecurityHeaders;
use Pdrs\Repository\AuditRepository;
use Pdrs\Repository\EventRepository;
use Pdrs\Repository\RateLimitRepository;
use Pdrs\Repository\RegistrationRepository;
use Pdrs\Repository\VerificationRepository;
use Pdrs\Service\ApprovalService;
use Pdrs\Service\AuditLogger;
use Pdrs\Service\CryptoService;
use Pdrs\Service\FieldMapper;
use Pdrs\Service\IcsService;
use Pdrs\Service\MoodleClient;
use Pdrs\Service\NotificationService;
use Pdrs\Service\RateLimiter;
use Pdrs\Service\RegistrationService;
use Pdrs\Service\VerificationService;
use Pdrs\Support\Database;
use Pdrs\Support\Env;
use Pdrs\Support\Json;
use Pdrs\Support\Validator;
use Pdrs\Support\View;

require __DIR__ . '/../src/bootstrap.php';

SecurityHeaders::apply();

$db = Database::connection();
$crypto = new CryptoService(AppConfig::appKey());
$events = new EventRepository($db);
$verifications = new VerificationService(
    new VerificationRepository($db),
    $crypto,
    new NotificationService()
);
$registrations = new RegistrationService(
    new RegistrationRepository($db),
    $crypto,
    new ApprovalService(),
    new FieldMapper(),
    new MoodleClient(),
    new NotificationService()
);
$rateLimiter = new RateLimiter(new RateLimitRepository($db), $crypto);
$audit = new AuditLogger(new AuditRepository($db), $crypto);
$router = new Router();

$router->get('/health', fn (): Response => Response::json(['status' => 'ok', 'service' => 'pdrs']));

$router->get('/', function (): Response {
    $body = '<section class="hero-card"><p class="eyebrow">PDRS Framework</p>';
    $body .= '<h1>Professional development registration built for secure institutional delivery.</h1>';
    $body .= '<p>Use event URLs such as <code>/e/secure-ai-governance</code> to publish controlled registration journeys.</p></section>';

    return new Response(View::render('PDRS Framework', $body));
});

$router->get('/e/{slug}', function (Request $request, array $params) use ($events): Response {
    $event = $events->findActiveBySlug($params['slug']);
    if (!$event) {
        return Response::json(['message' => 'Event not found'], 404);
    }

    return new Response(View::render($event['title'], renderEventPage($event)));
});

$router->post('/e/{slug}/verify', function (Request $request, array $params) use ($events, $rateLimiter, $verifications, $audit): Response {
    $event = $events->findActiveBySlug($params['slug']);
    if (!$event) {
        return Response::json(['message' => 'Event not found'], 404);
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

    return new Response(View::render('Verification sent', renderOtpForm($event, $email)));
});

$router->post('/e/{slug}/otp', function (Request $request, array $params) use ($events, $verifications, $audit): Response {
    $event = $events->findActiveBySlug($params['slug']);
    if (!$event) {
        return Response::json(['message' => 'Event not found'], 404);
    }

    $email = strtolower(trim((string) $request->input('email')));
    $otp = trim((string) $request->input('otp'));
    $challenge = $verifications->verifyOtp((int) $event['id'], $email, $otp);

    if (!$challenge) {
        $audit->record('verification.failed', $request, ['entity_type' => 'event', 'entity_id' => $event['id']]);
        return Response::json(['message' => 'Verification failed.'], 422);
    }

    $audit->record('verification.completed', $request, ['entity_type' => 'verification', 'entity_id' => $challenge['id']]);
    return new Response(View::render('Complete registration', renderRegistrationForm($event, $challenge)));
});

$router->get('/verify', function (Request $request) use ($events, $verifications, $audit): Response {
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
    return new Response(View::render('Complete registration', renderRegistrationForm($event, $challenge)));
});

$router->post('/e/{slug}/register', function (Request $request, array $params) use ($events, $verifications, $registrations, $audit): Response {
    $event = $events->findActiveBySlug($params['slug']);
    if (!$event) {
        return Response::json(['message' => 'Event not found'], 404);
    }

    $errors = Validator::required($request->post, ['verification_id', 'verification_signature', 'first_name', 'last_name']);
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

function renderEventPage(array $event): string
{
    $fields = renderCustomFields($event, false);
    $summary = View::e($event['summary']);
    $title = View::e($event['title']);
    $slug = View::e($event['slug']);
    $startAt = View::e($event['start_at']);
    $location = View::e($event['location']);

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
    <label>Email address <input required type="email" name="email" autocomplete="email"></label>
    {$fields}
    <button type="submit">Send verification</button>
  </form>
</section>
HTML;
}

function renderOtpForm(array $event, string $email): string
{
    $slug = View::e($event['slug']);
    $email = View::e($email);

    return <<<HTML
<section class="form-card">
  <h1>Check your email</h1>
  <p>A one-time code and signed verification link have been sent to {$email}.</p>
  <form method="post" action="/e/{$slug}/otp">
    <input type="hidden" name="email" value="{$email}">
    <label>One-time code <input required inputmode="numeric" name="otp" pattern="[0-9]{6}" maxlength="6"></label>
    <button type="submit">Verify and continue</button>
  </form>
</section>
HTML;
}

function renderRegistrationForm(array $event, array $challenge): string
{
    $slug = View::e($event['slug']);
    $fields = renderCustomFields($event, true);
    $verificationId = View::e((string) $challenge['id']);
    $verificationSignature = View::e($challenge['signature']);

    return <<<HTML
<section class="form-card">
  <h1>Complete registration</h1>
  <p>Your email is verified. Complete the remaining registration details for Moodle provisioning.</p>
  <form method="post" action="/e/{$slug}/register">
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
        $html .= "<label>{$label} <input {$required} name=\"{$name}\"></label>";
    }

    return $html;
}
