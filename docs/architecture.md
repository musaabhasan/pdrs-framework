# Architecture

## Design Goals

PDRS is structured around four priorities:

1. Protect identity data before it enters institutional systems.
2. Automate Moodle provisioning without creating duplicate user accounts.
3. Keep event configuration flexible enough for different professional development programs.
4. Leave a clear audit trail for registration, approval, and integration activity.

## Main Components

### Public Event Layer

Each event has a slug, metadata, delivery modes, custom fields, optional invite-code policy, Moodle course IDs, cohort IDs, approval rules, and publication status. The public route is:

```text
/e/{event-slug}
```

The default demo event is:

```text
/e/secure-ai-governance
```

### Verification Layer

The platform verifies email ownership before creating registration records. This reduces unnecessary PII collection and helps prevent duplicate or malicious submissions.

Supported verification methods:

- OTP code sent by email.
- Signed verification link sent by email.

Public POST routes use CSRF tokens and endpoint rate limits.

If invite-code access is enabled for the event, the invite code is validated before any verification challenge is issued. Only an HMAC hash of the invite code is stored.

### Registration Layer

After verification, the system collects standard user data and event-specific metadata:

- First name
- Last name
- City
- Custom event fields

Sensitive fields are encrypted before storage.

### Approval Layer

The approval service evaluates:

- Domain allow-list.
- Payment confirmation flag.
- Event-level instant approval setting.

Possible states:

- `pending`
- `approved`
- `provisioned`
- `failed`

### Moodle Integration Layer

The Moodle client calls Moodle REST web services to:

- Search for existing users.
- Create a user if no match exists.
- Enroll the user in one or more courses.
- Add the user to one or more cohorts.

Provisioning is handled by a dedicated service so failed enrollments can be retried from the command line without duplicating integration logic.

### Notification Layer

Notification events include:

- Verification email.
- Registration confirmation.
- Moodle credentials/access notice.
- Calendar invite download through `.ics`.

In local development, mail is written to `storage/logs/mail.log` and Mailpit is available through Docker. In production, notifications are sent through SMTP.

### Operations Layer

Operational endpoints and commands support production ownership:

- `/health` for liveness.
- `/readiness` for dependency readiness.
- `/ops/metrics` for protected platform metrics.
- `bin/maintenance.php` for cleanup and retention.
- `bin/retry-provisioning.php` for Moodle recovery.

## Request Flow

```text
GET /e/{slug}
  -> Render event page

POST /e/{slug}/verify
  -> Validate CSRF token
  -> Validate email
  -> Rate-limit request
  -> Validate invite code when enabled
  -> Issue OTP and signed link
  -> Log verification event

POST /e/{slug}/otp
  -> Validate CSRF token
  -> Rate-limit request
  -> Validate OTP
  -> Render final registration form

GET /verify?token=...
  -> Validate signed link
  -> Render final registration form

POST /e/{slug}/register
  -> Validate CSRF token
  -> Rate-limit request
  -> Validate verified challenge
  -> Create encrypted registration record
  -> Evaluate approval
  -> Provision Moodle access if approved
  -> Send confirmation
  -> Redirect to thank-you page
```

## Extension Points

- Add payment providers by extending `ApprovalService`.
- Add additional program delivery modes through event `program_modes`.
- Enable invite-only registrations by setting an event invite-code hash.
- Add admin UI modules backed by the repository layer.
- Add queue processing for reminders or long-running integrations.
- Add TOTP-based MFA for administrative accounts.
- Add additional Moodle functions in `MoodleClient`.
- Replace the simple view layer with Twig or another template engine.
