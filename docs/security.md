# Security and Data Protection

## Data Residency

Production deployments should host the application, MySQL database, object storage, backups, logs, and monitoring data in UAE-based infrastructure when required by organizational policy or data sovereignty obligations. Recommended deployment targets include approved UAE regions such as Azure UAE North.

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

The framework is currently stateless for public registration flows. When an administrative backend is added, enforce:

- Secure cookies.
- HTTP-only cookies.
- SameSite=Lax or Strict.
- Short idle timeout.
- MFA for all administrative users.

## Input Validation

The repository layer uses PDO prepared statements for all SQL queries. Public routes validate required fields, email syntax, and verification signatures.

## Rate Limiting

Verification endpoints are throttled by hashed IP address and email identifier. Configure:

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
