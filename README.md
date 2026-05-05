# Professional Development Registration System Framework

PDRS is a PHP 8.x and MySQL 8.0 framework for secure professional development registration, identity verification, and Moodle enrollment automation.

It is designed for education and enterprise environments where registration workflows must balance user experience, security, auditability, privacy, and operational maintainability.

## What It Provides

- Dynamic event landing pages using event slugs such as `/e/secure-ai-governance`.
- Event-specific metadata fields mapped to Moodle custom profile fields.
- Mandatory email verification before registration records are created.
- OTP and signed-link verification workflows.
- Session-backed CSRF protection across public form submissions.
- Duplicate identity checks against Moodle before account creation.
- Duplicate registration handling by event and verified email.
- Moodle REST integration for user creation, cohort assignment, and course enrollment.
- Retryable Moodle provisioning utility for operational recovery.
- Automatic approval based on domain allow-lists and payment status flags.
- AES-256-GCM encryption for sensitive registration data at rest.
- HMAC hashing for email, IP address, and user-agent lookups without exposing raw values.
- PDO prepared statements for all database operations.
- Rate limiting for verification endpoints.
- Audit logging for registration attempts, verification events, administrative changes, and integration failures.
- Liveness, readiness, and protected operations metrics endpoints.
- Maintenance commands for expired verification, rate-limit, and audit-log cleanup.
- SMTP transactional email transport with local mail logging for development.
- Trusted proxy configuration for accurate client IP handling behind load balancers.
- Rich field rendering and validation for text, email, number, date, textarea, and select inputs.
- Dockerized local development with PHP 8.3, Apache, MySQL 8.0, and Mailpit.

## Architecture

```text
Registrant
  -> Event landing page
  -> Email OTP or signed-link verification
  -> Registration form
  -> Approval policy
  -> Moodle identity check
  -> User creation if needed
  -> Cohort assignment and course enrollment
  -> Confirmation email and calendar invite
```

## Quick Start

```bash
cp .env.example .env
docker compose up --build
```

Then open:

- Application: `http://localhost:8080`
- Demo event: `http://localhost:8080/e/secure-ai-governance`
- Mailpit inbox: `http://localhost:8025`

Generate a strong local `APP_KEY` before testing encryption:

```bash
php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
```

If PHP is not installed locally, run that command inside a PHP container or replace the key with any secure 32-byte base64 value.

The example key in `.env.example` is only for local development and must be replaced before production use.

## Operational Commands

Run scheduled maintenance:

```bash
php bin/maintenance.php
```

Retry approved or failed Moodle provisioning records:

```bash
php bin/retry-provisioning.php
```

Generate an operations bearer token hash:

```bash
php -r "echo hash('sha256', 'replace-with-strong-token') . PHP_EOL;"
```

Set the result as `OPERATIONS_TOKEN_HASH` and call `/ops/metrics` with `Authorization: Bearer <token>`.

## Documentation

- [Architecture](docs/architecture.md)
- [Security and Data Protection](docs/security.md)
- [Security Controls](docs/security-controls.md)
- [Moodle Integration](docs/moodle-integration.md)
- [Database Schema](docs/database.md)
- [Development Guide](docs/development.md)
- [Operations Guide](docs/operations.md)
- [API and Routes](docs/api.md)
- [Extension Guide](docs/extension-guide.md)
- [Production Checklist](docs/production-checklist.md)
- [Testing Guide](docs/testing.md)
- [Future Roadmap](docs/roadmap.md)

## Repository Structure

```text
public/                 Web entry point and assets
src/                    Application code
src/Http                Request, response, and router
src/Repository          PDO database access layer
src/Service             Business services and integrations
database/migrations     MySQL schema and demo event seed
database/seeders        Optional standalone seed data
docs/                   Implementation and operations documentation
bin/                    Utility scripts
```

## Production Notes

Deploy the application and database to approved infrastructure for the institution, enforce TLS at the load balancer or reverse proxy layer, keep Moodle tokens in a secret manager, and restrict outbound access to approved Moodle endpoints and SMTP infrastructure.

## License

MIT License. See [LICENSE](LICENSE).
