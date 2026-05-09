# Secure Enrollment Workflow Threat Model

Use this threat model when approving a new professional development event, changing verification rules, expanding Moodle provisioning, or moving PDRS into a higher-volume institutional deployment. It focuses on the registration-to-enrollment path: public event pages, invite-code gates, email verification, registration storage, Moodle identity checks, cohort assignment, and operational recovery.

## Scope

| Boundary | In scope |
| --- | --- |
| Public registration | Event landing pages, invite codes, OTP or signed-link verification, form submission, duplicate checks |
| Data handling | Encrypted registration fields, HMAC lookup fields, custom event metadata, retention and disposal jobs |
| Moodle integration | User lookup, user creation, manual enrollment, cohort membership, provisioning retries |
| Operations | Metrics endpoint, maintenance jobs, retry jobs, SMTP delivery, audit logs, support investigations |
| Out of scope | Moodle core hardening, institutional identity provider design, payment gateway processing, LMS course content controls |

## Assets And Trust Boundaries

| Asset | Security objective | Trust boundary |
| --- | --- | --- |
| Invite codes | Prevent unauthorized access to restricted events | Public web to application |
| Email verification state | Prove mailbox control before registration creation | Public web to verification service |
| Registration record | Protect personal and event-specific data | Application to database |
| HMAC lookup values | Enable duplicate and rate-limit checks without raw identifiers | Application key boundary |
| Moodle token | Prevent unauthorized account, cohort, or enrollment changes | Application to Moodle API |
| SMTP credentials | Protect transactional messaging authority | Application to mail provider |
| Audit logs | Preserve defensible evidence without excessive personal data | Application to operations evidence store |
| Operations endpoint | Prevent public exposure of metrics and job state | Network edge to operations route |

## Threat Scenarios

| Scenario | Risk | Existing or expected control | Evidence to retain |
| --- | --- | --- | --- |
| Invite-code guessing | Unauthorized users reach restricted event registration | HMAC-stored invite codes, rate limiting, generic failures | Invite-code failure audit events |
| OTP or signed-link brute force | Attacker verifies a mailbox without control | Expiring challenges, attempt limits, hashed identifiers | Verification failure and completion logs |
| Duplicate identity confusion | Same learner is created twice or enrolled in wrong course | Moodle lookup by email and generated username before create | Provisioning audit record |
| Custom field over-collection | Event metadata collects unnecessary sensitive data | Event-specific field review and retention decision | Field review or event approval |
| Moodle token over-privilege | Compromise leads to broad LMS changes | Dedicated web service user with least-required functions | Moodle token scope record |
| Retry amplification | Failed provisioning is retried into duplicate or stale state | Idempotent retry command and bounded failure reasons | Retry run output and registration status |
| Support data leakage | Raw email, city, custom fields, or failure reasons are copied into tickets | Redaction guidance and minimal case summaries | Support case reference |
| Proxy header spoofing | Incorrect client IP affects rate limiting and audit trails | Trust proxy headers only behind controlled proxy | Deployment configuration review |
| Operations endpoint exposure | Metrics reveal sensitive operational state | Bearer-token hash and network restrictions | Operations access review |
| Audit-log over-retention | Logs keep unnecessary personal or sensitive details | Retention schedule and cleanup jobs | Maintenance run record |

## Control Review Checklist

| Control | Review question | Status |
| --- | --- | --- |
| Event approval | Is the event public, invite-only, paid, restricted, or high-risk? |  |
| Data minimization | Are custom fields necessary and documented? |  |
| Verification | Are OTP or signed-link expiry and attempt limits appropriate for event risk? |  |
| Duplicate prevention | Are Moodle lookup keys and registration duplicate checks enabled? |  |
| Moodle scope | Does the Moodle token expose only required web service functions? |  |
| Enrollment mapping | Are course IDs, cohort IDs, and role IDs approved before event launch? |  |
| Email delivery | Are SMTP credentials protected and message templates reviewed? |  |
| Retry process | Is failed provisioning monitored with an owner and due date? |  |
| Audit evidence | Are verification, registration, and provisioning events logged? |  |
| Retention | Are expired verification, rate-limit, and audit records cleaned on schedule? |  |

## Abuse Monitoring Signals

- Multiple invite-code failures for the same event slug.
- Repeated verification failures for one email hash or IP hash.
- Registration attempts with unusual custom field values or obvious automation patterns.
- Moodle provisioning failures concentrated on one event, cohort, or course.
- Retry jobs that repeatedly fail for the same registration.
- Operations endpoint requests from unexpected networks.
- Support cases requesting changes to registration identity after verification.

## Escalation Triggers

Escalate to security, privacy, or Moodle administration when:

- Invite-code or verification abuse creates a high-volume event.
- A Moodle token may be exposed or over-scoped.
- A registration is enrolled into the wrong course or cohort.
- Sensitive custom fields are collected without approval.
- An operations endpoint is reachable from an unapproved network.
- Audit or support evidence includes raw personal data beyond the approved audience.

## Review Cadence

Re-run this threat model before each high-volume event, after any Moodle API scope change, after changes to verification logic, and after any registration, provisioning, or support-data incident.
