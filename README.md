# Professional Development Registration System Framework

PDRS is a PHP 8.x and MySQL 8.0 framework for secure professional development registration, identity verification, and Moodle enrollment automation.

It is designed for regulated education and government-adjacent environments where registration workflows must balance user experience, security, auditability, data residency, and operational maintainability.

## What It Provides

- Dynamic event landing pages using event slugs such as `/e/secure-ai-governance`.
- Event-specific metadata fields mapped to Moodle custom profile fields.
- Mandatory email verification before registration records are created.
- OTP and signed-link verification workflows.
- Duplicate identity checks against Moodle before account creation.
- Moodle REST integration for user creation, cohort assignment, and course enrollment.
- Automatic approval based on domain allow-lists and payment status flags.
- AES-256-GCM encryption for sensitive registration data at rest.
- HMAC hashing for email, IP address, and user-agent lookups without exposing raw values.
- PDO prepared statements for all database operations.
- Rate limiting for verification endpoints.
- Audit logging for registration attempts, verification events, administrative changes, and integration failures.
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

## Documentation

- [Architecture](docs/architecture.md)
- [Security and Data Protection](docs/security.md)
- [Moodle Integration](docs/moodle-integration.md)
- [Database Schema](docs/database.md)
- [Development Guide](docs/development.md)
- [Operations Guide](docs/operations.md)
- [API and Routes](docs/api.md)
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

For UAE data residency requirements, deploy the application and database to UAE-hosted infrastructure such as Azure UAE North or an approved local hosting environment. Enforce TLS 1.3 at the load balancer or reverse proxy layer, keep Moodle tokens in a secret manager, and restrict outbound access to approved Moodle endpoints and SMTP infrastructure.

## License

MIT License. See [LICENSE](LICENSE).
