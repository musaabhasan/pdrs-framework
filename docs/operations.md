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

## Incident Response

If Moodle provisioning fails:

1. Keep the registration record.
2. Review `approval_status` and `approval_reason`.
3. Retry provisioning after validating Moodle token, course IDs, cohort IDs, and user data.
4. Record resolution in the audit trail.

## Maintenance Jobs

Add scheduled jobs for:

- Audit log retention enforcement.
- Expired verification cleanup.
- Pending registration reminders.
- Moodle provisioning retry queue.
- Event reminder emails.
