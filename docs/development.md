# Development Guide

## Prerequisites

- Docker Desktop
- Git

Local PHP and Composer are optional because the project runs in Docker.

## Setup

```bash
cp .env.example .env
docker compose up --build
```

Open:

- Application: `http://localhost:8080`
- Demo event: `http://localhost:8080/e/secure-ai-governance`
- Mailpit: `http://localhost:8025`

## Local Mail

In `APP_ENV=local`, messages are written to:

```text
storage/logs/mail.log
```

Mailpit is also available for SMTP testing.

## Code Organization

- `src/Http`: request, response, routing.
- `src/Repository`: database access through PDO.
- `src/Service`: business logic, encryption, Moodle, email, approval.
- `src/Middleware`: HTTP security, session, and operations access guards.
- `public`: front controller and CSS.
- `database`: MySQL schema and seed data.
- `docs`: implementation guidance.

## Quality Checks

Inside a PHP-enabled environment:

```bash
php bin/lint.php
php bin/self-test.php
php bin/test.php
```

The GitHub Actions workflow runs the same checks.

## Local Operations Testing

Generate a local operations token hash:

```bash
php -r "echo hash('sha256', 'local-operations-token') . PHP_EOL;"
```

Set `OPERATIONS_TOKEN_HASH` in `.env`, then call:

```bash
curl -H "Authorization: Bearer local-operations-token" http://localhost:8080/ops/metrics
```

Maintenance utilities can be run manually while developing:

```bash
php bin/maintenance.php
php bin/retry-provisioning.php
```

## Adding a New Event

Insert a row into `events` with:

- Unique `slug`.
- Event title and schedule.
- `custom_fields` JSON.
- Moodle course IDs.
- Optional cohort IDs.
- Approval rules.

For a future admin backend, use the repository layer rather than writing SQL directly in controllers.
