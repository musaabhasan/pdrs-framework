# API and Routes

## Public Routes

### `GET /`

Framework landing page.

### `GET /health`

Health endpoint.

Response:

```json
{
  "status": "ok",
  "service": "pdrs"
}
```

### `GET /e/{slug}`

Renders the public event landing page.

### `POST /e/{slug}/verify`

Starts email verification.

Required form fields:

- `email`

### `POST /e/{slug}/otp`

Verifies a one-time code.

Required form fields:

- `email`
- `otp`

### `GET /verify?token={token}`

Verifies a signed email link and renders the final registration form.

### `POST /e/{slug}/register`

Creates the registration after verification.

Required form fields:

- `verification_id`
- `verification_signature`
- `first_name`
- `last_name`

Optional standard field:

- `city`

Event custom fields are configured per event.

### `GET /thank-you/{uuid}`

Displays the registration confirmation page.

### `GET /calendar/{uuid}.ics`

Downloads the calendar invite.

## Future Admin Routes

Suggested future administrative modules:

- Event management.
- Field mapping.
- Approval review queue.
- Moodle provisioning retry queue.
- Audit search.
- Reporting dashboard.

All admin routes must require MFA.
