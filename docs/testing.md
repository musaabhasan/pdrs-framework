# Testing Guide

## Automated Checks

Run the core checks before every release:

```bash
php bin/lint.php
php bin/self-test.php
php bin/test.php
```

These checks cover syntax, encryption round trips, program delivery mode labels, invite-code policy checks, form validation, approval policy behavior, Moodle field mapping, CSRF validation, trusted proxy handling, route dispatching, operations-token authorization, and calendar invite generation.

## Integration Smoke Test

Use a MySQL 8 database with the migrations loaded, then start the PHP front controller:

```bash
php -S 127.0.0.1:8099 -t public public/index.php
```

Smoke-test this workflow:

- `GET /health`
- `GET /readiness`
- `GET /`
- `GET /e/secure-ai-governance`
- Invite-code disabled flow: verify email without an invite code.
- Invite-code enabled flow: reject a missing or invalid code, then accept a valid code.
- `POST /e/secure-ai-governance/verify`
- `POST /e/secure-ai-governance/otp`
- `POST /e/secure-ai-governance/register`
- `GET /thank-you/{uuid}`
- `GET /calendar/{uuid}.ics`
- `GET /ops/metrics` with and without the bearer token

Use an email domain outside the demo allow-list when testing without a live Moodle endpoint. This keeps the record in pending review and avoids external Moodle calls.

Generate invite-code hashes with:

```bash
php bin/hash-invite-code.php "program-invite-code"
```

## Release Evidence

Record the test date, database version, PHP version, commit hash, and any integration endpoints used. Keep screenshots or logs for the release record when the deployment has audit requirements.
