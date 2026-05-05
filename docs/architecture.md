# Architecture

## Design Goals

PDRS is structured around four priorities:

1. Protect identity data before it enters institutional systems.
2. Automate Moodle provisioning without creating duplicate user accounts.
3. Keep event configuration flexible enough for different professional development programs.
4. Leave a clear audit trail for registration, approval, and integration activity.

## Main Components

### Public Event Layer

Each event has a slug, metadata, custom fields, Moodle course IDs, cohort IDs, approval rules, and publication status. The public route is:

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

### Notification Layer

Notification events include:

- Verification email.
- Registration confirmation.
- Moodle credentials/access notice.
- Calendar invite download through `.ics`.

In local development, mail is written to `storage/logs/mail.log` and Mailpit is available through Docker.

## Request Flow

```text
GET /e/{slug}
  -> Render event page

POST /e/{slug}/verify
  -> Rate-limit request
  -> Validate email
  -> Issue OTP and signed link
  -> Log verification event

POST /e/{slug}/otp
  -> Validate OTP
  -> Render final registration form

GET /verify?token=...
  -> Validate signed link
  -> Render final registration form

POST /e/{slug}/register
  -> Validate verified challenge
  -> Create encrypted registration record
  -> Evaluate approval
  -> Provision Moodle access if approved
  -> Send confirmation
  -> Redirect to thank-you page
```

## Extension Points

- Add payment providers by extending `ApprovalService`.
- Add admin UI modules backed by the repository layer.
- Add queue processing for Moodle provisioning and reminders.
- Add TOTP-based MFA for administrative accounts.
- Add additional Moodle functions in `MoodleClient`.
- Replace the simple view layer with Twig or another template engine.
