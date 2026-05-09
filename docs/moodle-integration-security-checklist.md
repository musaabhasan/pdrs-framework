# Moodle Integration Security Checklist

Use this checklist before enabling Moodle provisioning for a new event, changing Moodle web service permissions, adding cohort assignment, or retrying failed enrollment records at scale. It is written for platform owners, Moodle administrators, security reviewers, and professional development operations teams.

## Review Header

| Field | Value |
| --- | --- |
| Event or program |  |
| Moodle site |  |
| PDRS environment | Development / Staging / Production |
| Moodle administrator |  |
| PDRS owner |  |
| Security reviewer |  |
| Review date |  |
| Next review due |  |

## Token And Web Service Scope

| Check | Evidence | Status |
| --- | --- | --- |
| Dedicated Moodle web service user is used instead of a human administrator account | Service user record |  |
| Token is restricted to approved REST functions | Moodle external service configuration |  |
| Token can perform only required lookup, create, enrollment, and cohort functions | Capability review |  |
| Token is stored outside source control and deployment artifacts | Secret manager or environment configuration |  |
| Token rotation owner and cadence are documented | Rotation record |  |
| Token revocation procedure is tested | Revocation test evidence |  |

Minimum expected Moodle functions:

```text
core_user_get_users_by_field
core_user_create_users
enrol_manual_enrol_users
core_cohort_add_cohort_members
```

## Identity Matching

| Check | Evidence | Status |
| --- | --- | --- |
| Email lookup is performed before creating a new Moodle user | Provisioning logic review |  |
| Generated username collisions are handled safely | Test evidence |  |
| Duplicate registration handling is active per event and verified email | Registration test |  |
| Moodle user reuse is logged without exposing unnecessary personal data | Audit log sample |  |
| Manual review path exists for ambiguous identity cases | Support workflow |  |

## Course And Cohort Mapping

| Check | Evidence | Status |
| --- | --- | --- |
| Course IDs are approved by program owner before launch | Event configuration review |  |
| Cohort IDs are approved by Moodle owner before launch | Event configuration review |  |
| Student role ID matches the target Moodle site | Role configuration |  |
| Event-specific custom fields map only to approved Moodle profile fields | Field mapping review |  |
| Test user is enrolled into the correct course and cohort in staging | Staging test evidence |  |
| Withdrawal or correction process is documented | Operations note |  |

## Provisioning Failure Handling

| Check | Evidence | Status |
| --- | --- | --- |
| Moodle API failures mark registrations as failed without losing registration evidence | Failed-state test |  |
| Failure reasons are bounded and safe for logs | Log review |  |
| Retry command is idempotent for existing Moodle users and enrollments | Retry test |  |
| Retry runs have an owner, schedule, and completion evidence | Operations record |  |
| Repeated failures escalate to Moodle or security owner | Escalation rule |  |
| Support team can distinguish registration success from Moodle provisioning success | Support guidance |  |

## Data Protection And Audit

| Check | Evidence | Status |
| --- | --- | --- |
| Moodle payload contains only fields needed for account creation and enrollment | Payload review |  |
| Registration data remains encrypted in PDRS storage | Database/security review |  |
| Email, IP, user-agent, and invite-code lookup fields use HMAC values | Data model review |  |
| Audit logs capture verification, registration, provisioning success, and provisioning failure | Audit sample |  |
| Raw Moodle token, OTPs, signed-link secrets, and invite codes are not logged | Log scan |  |
| Retention and cleanup jobs are scheduled | Maintenance evidence |  |

## Incident And Recovery Triggers

Escalate when:

- Moodle token exposure is suspected.
- Users are enrolled into the wrong course, cohort, or role.
- Repeated API failures affect a large event.
- Duplicate accounts are created unexpectedly.
- Support tickets expose raw registration or Moodle evidence.
- An attacker appears to be abusing invite, verification, or registration endpoints before Moodle provisioning.

## Go-Live Decision

| Decision | Criteria |
| --- | --- |
| Approve | Token scope, identity matching, mapping, retry, audit, and retention checks are complete. |
| Conditionally approve | Use is limited to a pilot event with named owners and remediation dates. |
| Hold | Token scope, mapping, duplicate handling, retry, or audit evidence is incomplete. |

| Final decision | Restrictions | Owner | Review date | Evidence reference |
| --- | --- | --- | --- | --- |
|  |  |  |  |  |
