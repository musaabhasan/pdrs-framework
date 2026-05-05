# Production Checklist

## Configuration

- Replace the local `APP_KEY` with a strong 32-byte value.
- Store secrets in a secret manager.
- Set `APP_ENV=production`.
- Set `APP_URL` to the public HTTPS URL.
- Set `SECURITY_FORCE_HTTPS=true`.
- Configure `OPERATIONS_TOKEN_HASH`.
- Configure SMTP host, port, encryption, credentials, and approved sender identity.
- Use a least-privilege Moodle web service token.
- Generate invite-code hashes after the production `APP_KEY` is set.

## Infrastructure

- Deploy application, database, backups, and logs to approved institutional infrastructure.
- Enforce TLS at the load balancer or reverse proxy.
- Restrict outbound traffic to Moodle and SMTP endpoints.
- Put `/ops/metrics` behind private network access where possible.
- Configure centralized application and audit logging.

## Database

- Use MySQL 8.0 with encrypted storage.
- Enable automated encrypted backups.
- Test restore procedures before go-live.
- Confirm database users have least-privilege permissions.

## Operations

- Schedule `php bin/maintenance.php`.
- Schedule `php bin/retry-provisioning.php` if Moodle is integrated.
- Monitor failed registrations and Moodle API exceptions.
- Monitor verification request spikes and rate-limit blocks.
- Review audit retention requirements with institutional policy owners.

## Release Readiness

- Run lint and self-test checks.
- Test registration through OTP and signed-link paths.
- Test invite-code disabled and invite-code enabled event flows.
- Test duplicate registration behavior.
- Test Moodle user lookup, user creation, course enrollment, and cohort assignment.
- Test the calendar invite download.
- Validate email sender reputation and SPF/DKIM/DMARC records.
