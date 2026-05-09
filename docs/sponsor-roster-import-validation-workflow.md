# Sponsor Roster Import Validation Workflow

This workflow defines how sponsor-provided participant rosters should be validated before invitations, registrations, Moodle provisioning, attendance tracking, or certificate release. It applies to employer-sponsored cohorts, partner-funded training, scholarship groups, internal departments, and bulk registration programs.

## Objectives

- Prevent incorrect enrollment caused by stale or malformed sponsor rosters.
- Confirm sponsor authority, eligibility rules, consent basis, identity fields, and event scope before import.
- Detect duplicate participants, name variants, invalid emails, payment or waiver mismatches, and invite-code misuse.
- Preserve audit evidence for sponsor changes, participant disputes, and certificate eligibility decisions.
- Minimize unnecessary personal data in roster files and import logs.

## Required Roster Metadata

| Field | Purpose |
| --- | --- |
| Sponsor name and owner | Identifies accountable organization and contact |
| Event ID or program slug | Prevents cross-event import mistakes |
| Roster version | Enables correction and rollback |
| Source timestamp | Shows when sponsor generated the roster |
| Approval reference | Confirms sponsor authority and program owner approval |
| Eligibility rule | Defines who should be invited or enrolled |
| Consent or notice basis | Confirms participants can be contacted and processed |
| Retention class | Defines how long roster evidence is kept |

## Participant Field Validation

| Field | Validation |
| --- | --- |
| Email | Required, normalized, domain-checked, and syntax-valid |
| Full name | Required where certificates or Moodle accounts are created |
| Arabic/local-script name | Required when bilingual certificate output depends on it |
| Sponsor participant ID | Required for bulk reconciliation where available |
| Department or cohort | Must map to approved event or Moodle cohort |
| Eligibility flag | Must be one of the approved values |
| Payment or waiver state | Must align with event approval rules |
| Accessibility or support flag | Must be minimized and handled through restricted workflow |

## Validation Steps

### 1. Confirm Sponsor Authority

1. Verify that the sponsor contact is approved for the event.
2. Confirm the roster file was delivered through an approved channel.
3. Check that the event, cohort, payment, and certificate rules match the sponsor agreement.
4. Record approval evidence before importing participants.

### 2. Inspect File Integrity

| Check | Expected Result |
| --- | --- |
| File hash captured | Import package has stable evidence |
| Header schema matches expected template | No unrecognized critical fields |
| Required columns present | No missing email, name, or eligibility fields |
| Duplicate headers absent | Parser behavior is deterministic |
| Formula-like cells neutralized | Spreadsheet injection risk reduced |
| Row count within approved limit | Bulk import matches expected cohort size |

### 3. Validate Participant Records

| Risk | Detection | Action |
| --- | --- | --- |
| Duplicate email | Same normalized email appears multiple times | Hold or merge by sponsor ID |
| Duplicate person | Same name and sponsor ID with different email | Identity exception workflow |
| Invalid email | Syntax, domain, or disposable-address issue | Reject row or request correction |
| Ineligible row | Eligibility flag conflicts with event rule | Hold row |
| Sponsor mismatch | Participant belongs to another sponsor or cohort | Escalate to program owner |
| Payment mismatch | Roster says sponsored but event requires payment evidence | Hold or verify waiver |
| Excessive personal data | Roster includes unnecessary ID, health, or HR fields | Redact and request corrected file |

### 4. Import Decision

| Decision | Use When | Follow-Up |
| --- | --- | --- |
| Import approved rows | Valid rows meet schema, eligibility, and consent rules | Send invitations or create pending registrations |
| Partial import | Some rows are valid and some require correction | Import valid rows, exception register for invalid rows |
| Hold import | Sponsor authority, consent, or schema is unclear | Request corrected evidence |
| Reject import | Roster is unauthorized, unsafe, or out of scope | Record decision and notify sponsor owner |

## Downstream Reconciliation

After import:

- compare imported rows to invitation and registration counts;
- confirm invite-code scope and expiry;
- reconcile verified registrations against sponsor roster;
- confirm Moodle provisioning does not create duplicate users;
- attach roster version to attendance and certificate eligibility checks;
- keep rejected and held rows in an exception register, not silent deletion.

## Privacy And Security Controls

- Store roster files outside the public web root.
- Hash email, IP, and user-agent fields where raw values are not needed.
- Redact unnecessary identity documents, HR IDs, or support notes.
- Restrict accessibility or accommodation fields to authorized support workflows.
- Delete or archive raw roster files according to the data retention policy.
- Log who uploaded, approved, imported, exported, or corrected a roster.

## Metrics

| Metric | Purpose |
| --- | --- |
| Roster rejection rate | Measures sponsor data quality |
| Duplicate row count | Detects identity or file preparation issues |
| Invalid email rate | Predicts invitation delivery failures |
| Held-row closure time | Measures operational responsiveness |
| Sponsor correction count | Tracks recurring sponsor-side issues |
| Moodle duplicate conflicts | Shows downstream provisioning risk |
| Certificate eligibility corrections tied to roster | Measures impact on credential accuracy |

## Closure Checklist

- Sponsor authority and program owner approval are recorded.
- File hash, schema, row count, and roster version are captured.
- Required participant fields pass validation or are listed as exceptions.
- Consent, eligibility, payment or waiver, and cohort scope are confirmed.
- Import results reconcile with invitations, registrations, Moodle provisioning, and certificate eligibility.
- Raw roster retention and redaction decisions are documented.
