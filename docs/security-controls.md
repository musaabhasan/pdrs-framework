# Security Controls

This matrix summarizes the baseline controls built into PDRS and the deployment controls expected in production.

| Area | Control | Implementation |
| --- | --- | --- |
| Identity verification | Email ownership before records | OTP and signed-link verification before registration creation |
| CSRF | Synchronizer token pattern | Session-backed `_csrf_token` on all public POST flows |
| Session security | Secure cookie posture | HTTP-only, SameSite=Lax, optional secure cookies |
| Input validation | Positive validation | Required fields, email syntax, field length limits, select option validation, typed custom fields |
| SQL injection defense | Parameterized access | PDO prepared statements in repositories |
| Sensitive data | Encryption at rest | AES-256-GCM for email, names, city, and event metadata |
| Searchable identifiers | Non-reversible lookup | HMAC-SHA-256 for email, IP address, and user-agent lookups |
| Duplicate prevention | Event-level uniqueness | Unique event/email hash constraint and Moodle identity lookup |
| Rate limiting | Abuse throttling | Verification, OTP, and registration submission throttles |
| Security headers | Browser hardening | CSP, frame denial, MIME sniffing protection, referrer policy, permissions policy, cross-origin policies |
| Transport | Encrypted traffic | TLS enforced at the load balancer or reverse proxy |
| Moodle API | Least privilege | Dedicated Moodle token limited to lookup, user creation, enrollment, and cohort assignment |
| Secrets | Secret isolation | Environment variables supplied from a secret manager in production |
| Auditability | Operational evidence | Audit events for verification, registration, rate limits, and integration failures |
| Observability | Production readiness | `/health`, `/readiness`, and protected `/ops/metrics` |
| Recovery | Integration retry | `bin/retry-provisioning.php` for Moodle provisioning recovery |
| Retention | Cleanup and disposal | `bin/maintenance.php` for expired verification, rate limit, and audit retention cleanup |

The control set is aligned with common application security guidance such as the OWASP [CSRF Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html) and [HTTP Headers Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/HTTP_Headers_Cheat_Sheet.html).
