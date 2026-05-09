# Identity Proofing Exception Workflow

This workflow defines how registration teams should handle identity-proofing exceptions before creating accounts, provisioning Moodle enrollment, approving attendance, or releasing certificates. It is designed for professional development programs where participants may have duplicate accounts, Arabic/English name variants, changed emails, sponsor-provided corrections, shared devices, or legitimate documentation gaps.

## Objectives

- Prevent account takeover, duplicate enrollment, and certificate misissuance.
- Support fair resolution for legitimate participants with name, email, sponsor, or documentation inconsistencies.
- Separate routine email verification from higher-risk manual proofing.
- Preserve audit evidence for identity decisions without retaining unnecessary personal documents.
- Align identity corrections with Moodle provisioning, attendance records, payment state, and certificate eligibility.

## Exception Triggers

| Trigger | Example | Initial Action |
| --- | --- | --- |
| Duplicate verified email | Same email appears in multiple registration records | Freeze duplicate provisioning until reviewed |
| Duplicate identity with different email | Same person registers using personal and work email | Link or merge under approved rule |
| Name variant | Arabic/English spelling difference, initials, title, or transliteration mismatch | Compare source records and participant confirmation |
| Sponsor correction | Employer or partner submits corrected participant details | Verify sponsor authority and participant notice |
| Moodle account conflict | Existing Moodle account has different email or identifier | Escalate to Moodle administrator |
| Payment or waiver mismatch | Paid/sponsored status does not align with identity | Check finance or sponsor evidence |
| Suspicious registration pattern | Shared IP, repeated device, disposable email, or rapid submissions | Apply risk review before approval |
| Certificate correction request | Participant asks to change name or identity detail after release | Use correction and reissue workflow |

## Proofing Levels

| Level | Use When | Evidence |
| --- | --- | --- |
| Level 0: Automated | Email verification and duplicate checks pass | Verification event and registration hash |
| Level 1: Support review | Minor spelling, email, or sponsor correction | Support case and participant confirmation |
| Level 2: Owner approval | Moodle conflict, duplicate identity, payment mismatch, or certificate impact | Program owner and Moodle/admin evidence |
| Level 3: Security escalation | Suspected impersonation, account takeover, fraud, or abuse pattern | Security case, containment, and decision record |

## Required Evidence

| Evidence | Purpose | Retention Note |
| --- | --- | --- |
| Registration reference | Links exception to event and participant | Retain under registration policy |
| Verified contact channel | Confirms participant can be reached | Store hashed or redacted where possible |
| Source-of-truth record | Sponsor roster, HR list, academic record, payment file, or prior account | Store reference instead of full document where possible |
| Moodle account evidence | Existing user ID, email, cohort, course, or conflict detail | Limit to necessary account fields |
| Support case | Participant request, correction, dispute, or explanation | Redact attachments after decision |
| Decision record | Approver, reason, outcome, and downstream updates | Retain with audit evidence |

## Review Steps

### 1. Freeze Risky Downstream Actions

When identity proofing is unresolved:

- hold Moodle account creation or course enrollment;
- hold certificate eligibility and release;
- prevent duplicate invite-code reuse where applicable;
- preserve current registration state;
- notify only approved operational owners.

### 2. Normalize Identity Fields

Compare:

- verified email;
- alternate email;
- full name in English;
- full name in Arabic or local script;
- organization or department;
- sponsor or employer reference;
- Moodle user ID;
- payment or waiver reference;
- registration timestamp and device/IP hash.

### 3. Classify The Exception

| Classification | Description |
| --- | --- |
| Benign correction | Typo, transliteration, title, department, or sponsor update |
| Duplicate but same person | Multiple records should be merged, linked, or one closed |
| Account conflict | Moodle or prior system record requires administrator review |
| Eligibility impact | Identity issue affects attendance, payment, or certificate release |
| Suspicious | Evidence suggests impersonation, abuse, or unauthorized registration |
| Inconclusive | Evidence is insufficient; hold until participant or sponsor responds |

### 4. Decide The Outcome

| Outcome | Use When | Required Follow-Up |
| --- | --- | --- |
| Approve as submitted | No material identity risk remains | Continue provisioning |
| Correct registration | Verified correction is minor and documented | Update audit log and notify participant |
| Merge or link records | Duplicate records belong to same person | Preserve superseded record reference |
| Reject or close duplicate | Duplicate or invalid record should not proceed | Record reason and appeal/support route |
| Escalate to security | Suspicious activity or attempted misuse | Open security case and containment action |
| Hold | Evidence is missing or disputed | Owner and due date required |

## Manual Proofing Controls

Manual proofing must include:

- two-person review for high-risk or certificate-impacting changes;
- reason code and source-of-truth reference;
- redaction of unnecessary document images or attachments;
- participant notice when personal details are changed;
- Moodle provisioning reconciliation after the decision;
- certificate correction or reissue path when a released certificate is affected.

## Privacy And Fairness Notes

- Do not require more identity evidence than the event risk requires.
- Accept legitimate Arabic/English spelling and transliteration differences when supported by source records.
- Avoid retaining government IDs, passports, or full HR documents when a reference or redacted proof is sufficient.
- Give participants a support or appeal route for rejected or held registrations.
- Apply the same proofing level consistently for similar exception types.

## Metrics

| Metric | Purpose |
| --- | --- |
| Identity exception rate | Detects registration form or sponsor data quality issues |
| Duplicate registration rate | Shows identity matching effectiveness |
| Manual proofing turnaround time | Measures participant impact |
| Moodle conflict count | Reveals integration or source-of-truth drift |
| Certificate-impacting corrections | Measures downstream quality risk |
| Security escalations | Tracks impersonation or abuse patterns |
| Reversal after dispute | Measures fairness and evidence quality |

## Closure Checklist

- Exception class, proofing level, evidence reference, and owner are recorded.
- Moodle, registration, payment, attendance, and certificate records are reconciled.
- Manual changes have reason, approver, timestamp, and downstream impact.
- Participant or sponsor communication is recorded where required.
- Unnecessary identity attachments are redacted or deleted under retention policy.
- Certificate correction or reissue workflow is triggered when released records are affected.
