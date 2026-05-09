# Moodle Enrollment Reconciliation Workflow

This workflow helps operators compare PDRS registration records with Moodle users, cohorts, and course enrollments after automated provisioning. Use it for daily operations, post-incident review, migration checks, end-of-cohort closure, or audit evidence when registration and learning-platform state must match.

## Objectives

- Detect approved registrations that were not provisioned in Moodle.
- Detect Moodle enrollments that do not have an approved PDRS source record.
- Confirm user identity matching without exposing raw personal data in operational reports.
- Identify cohort, course, role, and status drift before certificates, attendance, or completion reports are issued.
- Preserve evidence for retries, withdrawals, manual overrides, support cases, and audit review.

## Reconciliation Scope

| Scope Area | PDRS Source | Moodle Source | Expected Match |
| --- | --- | --- | --- |
| Identity | Verified registration email hash, external ID, approved registration ID | Moodle user ID, username, email, custom profile field | One active Moodle user per approved participant |
| Course enrollment | Approved event-course mapping | Moodle course enrollment and role assignment | Registered participant enrolled in mapped course with expected role |
| Cohort assignment | Event cohort metadata | Moodle cohort membership | Participant belongs to mapped cohort when cohort mode is enabled |
| Status | Registration status, approval status, withdrawal flag, retry status | Moodle enrollment status and suspension state | Active, withdrawn, suspended, or completed states align |
| Audit evidence | Provisioning log, retry log, support note | Moodle web service response, enrollment timestamp | Every state change has a traceable event |

## Required Inputs

| Input | Minimum Fields | Handling Notes |
| --- | --- | --- |
| Approved PDRS registrations | registration_id, event_id, verified_email_hash, approval_status, withdrawal_status, moodle_user_id, provisioning_status | Export from trusted database query or read-only reporting view |
| Moodle user export | moodle_user_id, username, email_hash or external_id, status, last_access | Prefer hashed email or institutional external ID over raw email |
| Moodle enrollment export | moodle_user_id, course_id, role, status, enrolled_at, suspended | Export from Moodle report or REST endpoint |
| Moodle cohort export | moodle_user_id, cohort_id, membership_status | Required when event delivery uses cohorts |
| Event mapping | event_id, moodle_course_id, moodle_cohort_id, expected_role | Treat as configuration evidence |
| Exception register | exception_id, participant_reference, reason, approver, expiry | Required for accepted mismatches |

## Match Rules

Use deterministic match keys before fuzzy comparison:

1. Match by stored `moodle_user_id` when PDRS previously provisioned the account.
2. Match by institutional external ID or Moodle custom profile field when present.
3. Match by HMAC email hash only when both exports use the same normalized hashing policy.
4. Use name-based matching only as a support investigation aid, never as an automatic enrollment action.

Normalization requirements:

- Lowercase and trim email addresses before hashing.
- Normalize Unicode whitespace in names before manual review.
- Preserve the original source value in the system of record, but use hashed or redacted values in reconciliation reports.
- Record the normalization version in the reconciliation evidence package.

## Drift Categories

| Category | Signal | Risk | Required Action |
| --- | --- | --- | --- |
| Missing Moodle user | Approved PDRS participant has no Moodle user match | Participant cannot access learning activity | Retry provisioning or escalate identity conflict |
| Duplicate Moodle identity | One PDRS participant maps to multiple Moodle users | Completion and certificate evidence may split across accounts | Hold certificate issuance and resolve identity ownership |
| Orphan Moodle enrollment | Moodle course enrollment has no approved PDRS source | Unauthorized access or manual bypass | Suspend or validate through approved exception |
| Course drift | Participant is enrolled in the wrong course | Incorrect learning path or compliance record | Correct course mapping and notify owner |
| Cohort drift | Participant missing expected cohort or assigned to wrong cohort | Broken announcements, reports, or completion grouping | Reassign cohort and record evidence |
| Role drift | Participant has teacher, manager, or elevated role instead of learner | Privilege escalation | Remove elevated role and escalate as security event |
| Withdrawal mismatch | Withdrawn participant remains active in Moodle | Access persists beyond approved participation | Suspend enrollment and update audit record |
| Retry backlog | Failed provisioning records are older than retry SLA | Operational failure and participant support impact | Run retry utility and escalate persistent failures |
| Completion conflict | Moodle completion exists for rejected or withdrawn registration | Certificate eligibility error | Hold certificate release and review manually |

## Daily Reconciliation Procedure

1. Export the approved PDRS participant set for events with active or recently closed delivery windows.
2. Export Moodle users, enrollments, cohorts, and roles for mapped courses and cohorts.
3. Apply the deterministic match rules and produce a comparison table.
4. Classify mismatches using the drift categories above.
5. Remove rows covered by approved, unexpired exceptions.
6. Route security-sensitive drift, such as elevated roles or orphan enrollments, to the security owner.
7. Route operational failures, such as missing users or retry backlog, to the registration support owner.
8. Remediate using approved Moodle APIs or documented manual procedures.
9. Re-run reconciliation after remediation and attach the before/after evidence to the incident, support case, or batch closure record.

## Evidence Table

| Evidence Item | Required Content | Retention Note |
| --- | --- | --- |
| Reconciliation run record | Run ID, operator, timestamp, event IDs, query versions, export hashes | Retain with operations evidence |
| PDRS export hash | Hash of source export or database snapshot reference | Do not retain full personal data longer than policy allows |
| Moodle export hash | Hash of Moodle users/enrollments/cohorts export | Redact raw email where possible |
| Drift report | Category, participant reference, event, course/cohort, owner, severity, action | Use pseudonymous participant references |
| Remediation record | API call, manual change, approver, timestamp, result | Link to audit log |
| Exception register | Reason, risk owner, approval, expiry, compensating control | Review expired exceptions before closure |
| Closure report | Remaining mismatches, accepted risk, next run date | Attach to cohort closure or audit package |

## Severity and Routing

| Severity | Criteria | Owner |
| --- | --- | --- |
| Critical | Elevated Moodle role, orphan enrollment in restricted course, cross-event identity collision, or evidence of unauthorized access | Security owner and Moodle administrator |
| High | Approved participant cannot access required course, withdrawn participant remains active, duplicate identity affects completion or certificate eligibility | Registration operations owner |
| Medium | Cohort/reporting drift, stale retry queue, missing completion sync without access impact | Program coordinator |
| Low | Documentation mismatch, accepted timing difference, nonblocking report label issue | Support queue |

## Safe Remediation Rules

- Do not create or merge Moodle accounts from name similarity alone.
- Do not delete Moodle users as a first response; suspend, unlink, or investigate unless retention policy allows deletion.
- Do not enroll users into restricted courses without approval evidence in PDRS.
- Do not expose raw email, IP address, or user-agent data in shared reconciliation reports.
- Do not issue certificates until enrollment, completion, withdrawal, and exception states are reconciled.
- Record every manual override with approver, reason, expiry, and affected course/cohort.

## Certificate and Completion Release Gate

Before certificate issuance or completion reporting, confirm:

| Check | Pass Criteria | Evidence |
| --- | --- | --- |
| All approved participants have matching Moodle enrollment | No unresolved missing-user or missing-enrollment drift | Reconciliation run |
| No orphan enrollments affect the course | All unmatched enrollments are removed or approved exceptions | Drift report |
| Withdrawals are reflected in Moodle | Withdrawn participants are suspended or removed according to policy | Moodle state export |
| Completion records map to approved registrations | Completion is linked to the expected PDRS participant and event | Completion export |
| Exceptions are current | No expired exception is used to approve a mismatch | Exception register |
| Remediation was retested | After-action reconciliation shows closure or approved residual risk | Closure report |

## Recommended Metrics

| Metric | Purpose |
| --- | --- |
| Missing Moodle user count | Measures provisioning reliability |
| Orphan enrollment count | Indicates access governance risk |
| Duplicate identity count | Highlights identity integrity issues |
| Retry backlog age | Tracks operational recovery performance |
| Manual override count | Detects process friction or integration gaps |
| Time to remediate critical drift | Measures response maturity |
| Certificate release holds caused by LMS drift | Connects reconciliation to learning-record assurance |

## Closure Record

| Field | Response |
| --- | --- |
| Reconciliation run ID |  |
| Events reviewed |  |
| Moodle courses/cohorts reviewed |  |
| Critical findings |  |
| High findings |  |
| Accepted exceptions |  |
| Remediation completed |  |
| Residual risk owner |  |
| Certificate or completion release decision | Approve / hold / approve with conditions |
| Next scheduled reconciliation |  |
