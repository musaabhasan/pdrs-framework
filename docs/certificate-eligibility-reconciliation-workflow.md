# Certificate Eligibility Reconciliation Workflow

This workflow verifies that professional development certificates are released only when registration, approval, attendance, Moodle completion, payment, withdrawal, exception, and identity evidence agree. Use it before certificate generation, certificate reissue, cohort closure, or audit reporting.

## Objectives

- Prevent certificates for rejected, withdrawn, duplicate, unpaid, or incomplete registrations.
- Confirm Moodle completion records map to the correct PDRS participant and event.
- Detect manual overrides and expired exceptions before certificate release.
- Preserve evidence for disputes, correction requests, and audit review.
- Minimize personal data in reconciliation reports.

## Required Data Sources

| Source | Minimum Fields | Purpose |
| --- | --- | --- |
| PDRS registration export | registration_id, event_id, verified_email_hash, approval_status, registration_status, payment_status, withdrawal_status | Source of eligibility intent |
| Moodle enrollment/completion export | moodle_user_id, course_id, completion_status, completion_date, grade or activity status | Learning completion evidence |
| Attendance record | registration_id or participant reference, session ID, attendance status, timestamp | Synchronous and hybrid participation evidence |
| Event rule record | event_id, certificate rule, attendance threshold, completion requirement, payment requirement | Eligibility policy |
| Exception register | exception_id, participant reference, reason, approver, expiry, compensating evidence | Approved nonstandard eligibility |
| Certificate batch manifest | certificate number, recipient reference, issue date, template version, hash | Release evidence |

## Eligibility Rules

| Rule | Pass Criteria | Blocking Condition |
| --- | --- | --- |
| Verified identity | Registration email or external ID is verified and maps to one participant | Unverified or duplicate identity |
| Approved registration | Registration is approved or covered by active exception | Rejected, pending, or missing approval |
| Not withdrawn | Participant is active or withdrawal is reversed with evidence | Withdrawn or cancelled participant |
| Payment condition | Paid, waived, or payment not required | Unpaid when payment is required |
| Attendance condition | Meets event attendance threshold | Insufficient attendance without exception |
| Moodle completion | Required course or activity is completed | Incomplete or mismatched Moodle record |
| Exception validity | Exception is approved and unexpired | Expired, unapproved, or vague exception |
| No duplicate certificate | No active certificate already exists unless reissue is approved | Duplicate active certificate |

## Reconciliation Procedure

1. Export the PDRS approved participant set for the target event or cohort.
2. Export Moodle completion and enrollment records for mapped courses.
3. Export attendance evidence for sessions that contribute to certificate eligibility.
4. Load event certificate rules and threshold requirements.
5. Exclude rejected, cancelled, withdrawn, and duplicate registrations unless an active exception applies.
6. Match Moodle records using stored Moodle user ID, external ID, or normalized email hash.
7. Evaluate each eligibility rule and classify findings.
8. Route high-severity mismatches to the program owner before certificate generation.
9. Generate a release candidate list and attach evidence references.
10. Re-run reconciliation after remediation and before final certificate batch approval.

## Mismatch Categories

| Category | Signal | Required Action |
| --- | --- | --- |
| Identity mismatch | Registration maps to multiple Moodle users or no Moodle user | Hold certificate and resolve identity |
| Registration not approved | Certificate candidate has pending, rejected, or cancelled registration | Remove from batch |
| Withdrawal conflict | Participant withdrew but appears in certificate candidate list | Hold and verify reinstatement evidence |
| Completion mismatch | Moodle completion missing, incomplete, or linked to wrong course | Hold and review Moodle evidence |
| Attendance shortfall | Attendance below threshold | Hold unless active exception exists |
| Payment block | Payment required but unpaid | Hold until payment or waiver evidence exists |
| Expired exception | Candidate relies on expired or unapproved exception | Hold and renew or remove exception |
| Duplicate release | Active certificate already issued | Reissue workflow required |

## Evidence Table

| Participant Ref | Registration Status | Payment | Attendance | Moodle Completion | Exception | Certificate Decision | Notes |
| --- | --- | --- | --- | --- | --- | --- | --- |
|  | Approved / pending / rejected / withdrawn | Paid / unpaid / waived / not required | Pass / fail / not required | Pass / fail / not required | Active / expired / none | Release / hold / reissue review |  |

Use pseudonymous participant references in shared reports. Keep raw personal data in restricted evidence storage.

## Severity Routing

| Severity | Criteria | Owner |
| --- | --- | --- |
| Critical | Duplicate certificate, wrong recipient, identity collision, or certificate for withdrawn/rejected participant | Program owner and operations lead |
| High | Missing completion, attendance shortfall, expired exception, or unpaid required fee | Program coordinator |
| Medium | Evidence reference missing, delayed Moodle sync, or manual override needs review | Registration support owner |
| Low | Formatting, reporting label, or nonblocking metadata issue | Support queue |

## Certificate Release Gate

Before generating PDFs or sending certificates:

| Check | Pass Criteria |
| --- | --- |
| Release candidate list is reconciled | No unresolved critical or high findings |
| Moodle completion is matched | Required records map to the correct participant and event |
| Attendance threshold is met | Attendance evidence meets certificate rule |
| Payment and waiver status is clear | No payment-required candidate is unpaid |
| Exceptions are active | No expired exception is used for release |
| Duplicate certificate check is complete | Existing active certificate is not duplicated |
| Batch manifest will be generated | Certificate numbers and hashes will be recorded |

## Correction and Reissue Linkage

If a certificate was released incorrectly:

1. Mark the original certificate as under review.
2. Preserve the original certificate hash and batch manifest reference.
3. Reconcile the participant using current evidence.
4. Decide whether to revoke, supersede, or reissue.
5. Notify the recipient or stakeholder where policy requires it.
6. Record replacement certificate number, reason, approver, and issue date.

## Closure Record

| Field | Response |
| --- | --- |
| Event or cohort |  |
| Reconciliation run ID |  |
| Candidate count |  |
| Released count |  |
| Held count |  |
| Exception count |  |
| Duplicate or reissue count |  |
| Evidence package location |  |
| Release approver |  |
| Next review trigger |  |
