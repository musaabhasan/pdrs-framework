# API and Routes

## Public Routes

### `GET /`

Framework landing page.

### `GET /health`

Liveness endpoint for load balancers and uptime monitors.

Response:

```json
{
  "status": "ok",
  "service": "pdrs"
}
```

### `GET /readiness`

Readiness endpoint for deployment and orchestration checks. It validates database access, writable storage paths, application key strength, and Moodle configuration presence.

Healthy deployments return HTTP 200 with `status: ready`. Degraded deployments return HTTP 503 with `status: degraded`.

### `GET /ops/metrics`

Protected operational metrics endpoint. Requires:

```text
Authorization: Bearer <operations-token>
```

The token is checked against `OPERATIONS_TOKEN_HASH`, which must contain the SHA-256 hash of the bearer token.

Response includes event counts, registration counts by status, open verification challenges, expired challenges, and recent audit volume.

### `GET /e/{slug}`

Renders the public event landing page.

### `POST /e/{slug}/verify`

Starts email verification.

Required form fields:

- `_csrf_token`
- `email`

Additional form field required only when event invite-code access is enabled:

- `invite_code`

Invite-code validation happens before an OTP or signed-link challenge is issued.

### `POST /e/{slug}/otp`

Verifies a one-time code.

Required form fields:

- `_csrf_token`
- `email`
- `otp`

### `GET /verify?token={token}`

Verifies a signed email link and renders the final registration form.

### `POST /e/{slug}/register`

Creates the registration after verification.

Required form fields:

- `_csrf_token`
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
