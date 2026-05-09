# Data Retention And Privacy Workflow

Use this workflow before launching a professional development event, changing registration fields, connecting Moodle provisioning, issuing certificates, or exporting operational evidence. PDRS handles personal data across registration, verification, approval, enrollment, attendance, completion, support, and audit processes; each stage needs a clear retention and privacy decision.

## Review Header

| Field | Value |
| --- | --- |
| Event or program |  |
| Delivery mode | Synchronous / Asynchronous / Hybrid / Self-paced / Other |
| PDRS environment | Development / Staging / Production |
| Data owner |  |
| Moodle owner |  |
| Privacy reviewer |  |
| Security reviewer |  |
| Review date |  |
| Next review due |  |

## Data Inventory

| Data Category | Examples | Classification | Owner | Default Retention |
| --- | --- | --- | --- | --- |
| Registration profile | Name, email, organization, role, event selections | Internal / Confidential | Program owner | Event period plus approved operational window |
| Verification artifacts | OTP attempts, signed-link tokens, verification timestamps | Confidential | Platform owner | Short operational window |
| Invite and abuse controls | Invite-code hashes, IP HMAC, user-agent HMAC, rate-limit records | Confidential | Security owner | Short operational window unless incident hold applies |
| Moodle provisioning evidence | Moodle user ID, course ID, cohort ID, status, failure reason | Internal / Confidential | Moodle owner | Enrollment support window plus audit window |
| Payment or approval flags | Approval state, payment confirmation, eligibility evidence | Confidential | Program owner | Finance or eligibility retention period |
| Attendance and completion | Attendance status, completion state, certificate eligibility | Internal / Confidential | Program owner | Certification or learning-record retention period |
| Certificate evidence | Certificate number, issue date, verification token hash, PDF hash | Internal / Confidential | Certificate owner | Certificate validity plus audit window |
| Support records | Correction requests, failed enrollment tickets, email disputes | Confidential | Support owner | Support case retention period |
| Audit logs | Administrative changes, verification events, provisioning retries | Confidential | Security owner | Security audit retention period |

## Collection Minimization Gate

| Question | Decision |
| --- | --- |
| Is each registration field required for eligibility, communication, attendance, Moodle enrollment, or certificate issuance? |  |
| Can optional fields be removed, deferred, or collected only after approval? |  |
| Are event-specific custom fields mapped to approved Moodle profile fields only? |  |
| Are sensitive fields excluded from email bodies, URLs, exports, and support screenshots? |  |
| Are hashed lookup fields used instead of raw email, IP address, invite code, or user-agent values where possible? |  |
| Are test, demo, and staging datasets synthetic or minimized? |  |

## Retention Decision Matrix

| Record Type | Retain | Delete Or Anonymize | Legal Hold Trigger | Evidence Owner |
| --- | --- | --- | --- | --- |
| Unverified registration attempts |  |  |  |  |
| Verified but incomplete registrations |  |  |  |  |
| Approved registration records |  |  |  |  |
| Rejected or cancelled registrations |  |  |  |  |
| Moodle provisioning failures |  |  |  |  |
| Successful enrollment evidence |  |  |  |  |
| Certificate issuance evidence |  |  |  |  |
| Support and correction records |  |  |  |  |
| Administrative audit logs |  |  |  |  |
| Rate-limit and abuse records |  |  |  |  |

## Lifecycle Controls

| Stage | Privacy Control | Security Control | Evidence |
| --- | --- | --- | --- |
| Event design | Minimize fields and define retention before publishing | Review invite-code, approval, and Moodle mappings | Event configuration review |
| Verification | Expire OTPs and signed links quickly | Rate-limit verification attempts and store secrets safely | Verification audit sample |
| Registration | Store only approved fields | Encrypt sensitive records and use prepared statements | Database and field review |
| Approval | Limit reviewer access to event scope | Log approval decisions and role changes | Approval audit sample |
| Moodle provisioning | Send only required account and enrollment fields | Use scoped Moodle token and retry safely | Provisioning log sample |
| Attendance and completion | Keep attendance evidence only for approved purposes | Restrict exports and certificate eligibility updates | Completion evidence |
| Certificate issuance | Store verification hashes, not raw secrets | Protect PDF, certificate hash, and verification token lifecycle | Certificate evidence |
| Support | Redact unrelated personal data from tickets | Restrict access to correction and dispute evidence | Support case sample |
| Retention cleanup | Delete or anonymize expired records | Log cleanup job results and exceptions | Maintenance evidence |

## Deletion And Anonymization Checklist

- Expired verification tokens and OTPs are deleted or invalidated.
- Failed or abandoned registrations are deleted or minimized after the approved window.
- Rate-limit and abuse records are retained only while needed for security review.
- Moodle provisioning failures are closed, retried, or anonymized according to support needs.
- Certificate verification records preserve only safe public metadata and hashed tokens.
- Support cases are redacted before sharing with vendors or program teams.
- Audit logs retain security-relevant facts without raw secrets, OTPs, or invite codes.
- Legal holds, open disputes, or active incidents pause deletion for the specific records in scope.

## Access Review

| Role | Allowed Data | Prohibited Data | Review Cadence |
| --- | --- | --- | --- |
| Program administrator | Event registration and approval fields | Raw secrets, invite codes, platform credentials |  |
| Moodle administrator | Enrollment identifiers and provisioning state | Unrelated registration fields |  |
| Support agent | Case-specific status and approved contact fields | Bulk exports and raw verification secrets |  |
| Security reviewer | Audit logs, abuse signals, HMAC fields | Unrelated learning records unless incident scope requires |  |
| Database administrator | Operational database access under change control | Business use of personal data |  |

## Incident And Exception Triggers

Escalate to privacy and security review when:

- a field is added without a documented purpose,
- raw registration exports are shared outside the approved owner group,
- support tickets include unnecessary personal data,
- Moodle provisioning sends data to the wrong course, cohort, or account,
- retention cleanup fails or records remain after the approved window,
- an incident, legal hold, or learner dispute requires retention beyond normal policy,
- a vendor or external platform requests data not covered by the original purpose.

## Go-Live Decision

| Decision | Criteria |
| --- | --- |
| Approve | Data inventory, minimization, access review, retention, deletion, and evidence owners are complete. |
| Conditionally approve | Use is limited to a pilot or narrow event with named remediation owners and dates. |
| Hold | Purpose, retention, Moodle data transfer, deletion, or access controls are incomplete. |

| Final decision | Conditions | Owner | Review date | Evidence reference |
| --- | --- | --- | --- | --- |
|  |  |  |  |  |
