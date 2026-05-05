# Database Schema

The database is MySQL 8.0 with `utf8mb4` collation.

## Tables

### `events`

Stores professional development events, public slugs, custom fields, approval policy, and Moodle mapping.

Important columns:

- `slug`
- `custom_fields`
- `allowed_domains`
- `moodle_course_ids`
- `moodle_cohort_ids`
- `instant_approval`
- `requires_payment`

### `verification_challenges`

Stores OTP and signed-link verification challenges before registration records are created.

Important columns:

- `email_hash`
- `email_encrypted`
- `code_hash`
- `signed_token_hash`
- `expires_at`
- `verified_at`
- `attempts`

### `registrations`

Stores encrypted registration data and Moodle provisioning state.

Important columns:

- `email_hash`
- `email_encrypted`
- `first_name_encrypted`
- `last_name_encrypted`
- `metadata_encrypted`
- `approval_status`
- `moodle_user_id`
- `provisioned_at`

The unique event/email hash constraint prevents duplicate registrations for the same event while avoiding raw email storage in indexed columns.

### `audit_logs`

Stores operational and security audit events.

### `rate_limits`

Stores throttle counters for verification and registration endpoints.

## Migrations

Migrations are stored in:

```text
database/migrations
```

Docker initializes the database from this folder during first startup.

## Retention

Audit logs should be retained for 12 months unless institutional policy requires longer retention. Registration data retention should be defined by the program owner and data protection office.

Run `php bin/maintenance.php` on a schedule to remove expired verification challenges, prune old rate-limit windows, and enforce audit retention.
