# Security and Data Protection

## Hosting and Data Governance

Production deployments should host the application, MySQL database, object storage, backups, logs, and monitoring data in infrastructure approved by the institution. Confirm hosting, retention, backup, and disposal requirements before go-live.

## Sensitive Data Handling

PDRS does not create registration records until email ownership is verified.

Stored sensitive fields are encrypted with AES-256-GCM:

- Email address
- First name
- Last name
- City
- Event metadata

Lookup fields use HMAC-SHA-256:

- Email address
- IP address
- User agent

This supports duplicate checks and rate limiting without exposing raw values in indexed columns.

## Transport Security

Production traffic should enforce:

- TLS 1.3 where supported.
- HSTS at the reverse proxy or load balancer.
- HTTPS-only cookies.
- Modern cipher suites.

## Session and Cookie Security

Public registration flows use secure session configuration and CSRF tokens for all POST requests. Session cookies are HTTP-only, SameSite=Lax, and can be forced to HTTPS through `SECURITY_FORCE_HTTPS=true`.

When an administrative backend is added, enforce:

- Secure cookies.
- HTTP-only cookies.
- SameSite=Lax or Strict.
- Short idle timeout.
- MFA for all administrative users.

## Input Validation

The repository layer uses PDO prepared statements for all SQL queries. Public routes validate required fields, email syntax, verification signatures, custom required fields, custom field types, select options, field length limits, and CSRF tokens.

## Client IP Handling

By default, PDRS uses `REMOTE_ADDR`. Enable `TRUST_PROXY_HEADERS=true` only when the application is behind a trusted load balancer or reverse proxy that controls `X-Forwarded-For`.

## Rate Limiting

Verification, OTP, and registration submission endpoints are throttled by hashed IP address and email identifier. Configure:

```text
RATE_LIMIT_WINDOW_SECONDS=900
RATE_LIMIT_MAX_ATTEMPTS=5
```

## Least Privilege Moodle Token

Create a dedicated Moodle web service user and token. Grant only the capabilities required for:

- User lookup.
- User creation.
- Manual enrollment.
- Cohort membership assignment, if cohorts are used.

Do not reuse administrator tokens.

## Audit Logging

PDRS records:

- Verification issuance.
- Verification failures and completions.
- Registration creation.
- Moodle provisioning failures.
- Future administrative changes.

Audit logs should be retained for 12 months unless a stricter institutional policy applies.

## Operations Endpoint Protection

`/ops/metrics` is disabled unless `OPERATIONS_TOKEN_HASH` is configured. Store only the SHA-256 hash of a strong bearer token in the environment. Keep the raw token in a secret manager and restrict endpoint exposure through network controls.

## Production Hardening Checklist

- Store `APP_KEY`, database credentials, SMTP credentials, and Moodle token in a secret manager.
- Disable display of PHP errors.
- Route application logs to centralized monitoring.
- Apply WAF rules for registration and verification endpoints.
- Restrict outbound network access to Moodle and SMTP endpoints.
- Run regular dependency and container image scans.
- Enforce encrypted database backups.
- Test restore procedures.
- Define data retention and disposal jobs.
- Schedule `bin/maintenance.php`.
- Schedule `bin/retry-provisioning.php` when Moodle availability is not guaranteed.
