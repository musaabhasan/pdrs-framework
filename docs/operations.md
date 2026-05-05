# Operations Guide

## Recommended Production Topology

```text
WAF / Load Balancer
  -> PHP application containers
  -> MySQL 8.0 managed database
  -> Moodle REST endpoint
  -> SMTP transactional email service
  -> Central log and monitoring platform
```

## UAE Data Residency

Deploy application, database, logs, backups, and monitoring data to approved UAE infrastructure when required. Azure UAE North is an example target, subject to organizational approval.

## Secrets

Keep these in a secret manager:

- `APP_KEY`
- database password
- Moodle token
- SMTP credentials

Never commit `.env` files.

## Backups

Back up:

- MySQL database.
- Application configuration.
- Audit logs, if exported to external storage.

Backups must be encrypted and tested through periodic restore drills.

## Monitoring

Recommended signals:

- Verification request rate.
- Rate-limit blocks.
- Moodle API failure count.
- Registration completion rate.
- Pending registrations older than SLA.
- Failed provisioning records.
- Audit log ingestion health.

Use `/health` for liveness and `/readiness` for dependency readiness. Use `/ops/metrics` only from trusted networks and only with a configured bearer token hash.

## Transactional Email

Production notifications are sent through SMTP. Configure `SMTP_HOST`, `SMTP_PORT`, `SMTP_ENCRYPTION`, credentials when required, and sender identity. Supported encryption values:

- `none` for trusted local relays.
- `starttls` for explicit TLS, usually on port 587.
- `tls` for implicit TLS, usually on port 465.

In `APP_ENV=local`, messages are written to `storage/logs/mail.log` for predictable development testing.

## Incident Response

If Moodle provisioning fails:

1. Keep the registration record.
2. Review `approval_status` and `approval_reason`.
3. Retry provisioning after validating Moodle token, course IDs, cohort IDs, and user data.
4. Record resolution in the audit trail.

Retry command:

```bash
php bin/retry-provisioning.php
```

## Maintenance Jobs

Recommended scheduled jobs:

- `php bin/maintenance.php` every night.
- `php bin/retry-provisioning.php` every 5 to 15 minutes when Moodle is integrated.
- Pending registration reminders, once reminder templates are introduced.
- Event reminder emails, once reminder templates are introduced.

## Operations Token

Generate a strong token and store only its SHA-256 hash:

```bash
php -r "echo hash('sha256', 'replace-with-strong-token') . PHP_EOL;"
```

Set the generated value as `OPERATIONS_TOKEN_HASH`. Store the raw token in a secret manager or deployment platform secret.
