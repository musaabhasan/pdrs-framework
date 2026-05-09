# Attendance Anomaly Triage Workflow

This workflow defines how professional development teams should investigate attendance anomalies before making completion, Moodle enrollment, payment, or certificate eligibility decisions. It supports in-person, virtual, hybrid, cohort-based, and self-paced events where attendance evidence may come from check-in systems, meeting platforms, LMS activity, instructor records, QR scans, support cases, and manual overrides.

## Objectives

- Prevent unfair certificate denial or approval caused by incomplete attendance evidence.
- Detect duplicate check-ins, late joins, impossible attendance windows, platform drift, and manual override risk.
- Align registration, verification, Moodle, attendance, payment, withdrawal, and certificate evidence.
- Preserve a clear exception trail for participant disputes and audit review.
- Minimize retention of unnecessary attendance telemetry after eligibility decisions are closed.

## Attendance Evidence Sources

| Source | Examples | Key Limitation |
| --- | --- | --- |
| Registration system | Verified email, registration state, approval status | Does not prove attendance |
| In-person check-in | QR scan, badge scan, signature sheet, facilitator entry | Proxy check-in or missing checkout |
| Virtual platform | Join time, leave time, attention duration, device count | Reconnects, shared devices, platform clock differences |
| Moodle activity | Course access, quiz attempts, resource completion | Activity may be asynchronous or automated |
| Instructor record | Participation sheet, lab completion, assessment notes | Manual error or subjective judgment |
| Support case | Connection issue, accommodation, late approval, correction request | Must be verified and scoped |
| Payment or sponsor record | Paid, sponsored, waived, refunded | Financial status does not prove attendance |

## Anomaly Types

| Anomaly | Review Trigger | Typical Action |
| --- | --- | --- |
| Missing attendance | Approved participant has no attendance record | Check platform logs, support cases, and instructor records |
| Duplicate check-in | Multiple records for same person, email, device, or badge | Merge or flag for identity review |
| Late join or early leave | Duration below completion threshold | Review policy, support case, and session requirements |
| Impossible overlap | Participant appears in overlapping sessions or locations | Confirm schedule, timezone, and identity evidence |
| Platform drift | Meeting platform and Moodle disagree materially | Normalize timestamps and review source reliability |
| Manual override | Attendance changed outside automated evidence | Require reason, approver, and evidence reference |
| Withdrawn but attended | Withdrawal state conflicts with attendance evidence | Confirm user intent and certificate policy |
| Paid but absent | Payment complete but attendance not met | Apply published eligibility rule |
| No-show but active in Moodle | Event attendance and asynchronous completion conflict | Determine whether event supports asynchronous completion |

## Triage Steps

### 1. Build The Evidence Snapshot

1. Freeze the triage window and event timezone.
2. Export registration, approval, attendance, Moodle activity, payment, withdrawal, and support case references.
3. Hash or redact direct identifiers in working files where raw values are not needed.
4. Capture source system, export timestamp, owner, and query criteria for each evidence set.

### 2. Normalize Identity And Time

| Check | Expected Result |
| --- | --- |
| Email and verified account match | Participant maps to one verified identity |
| Moodle user ID alignment | LMS record maps to the same participant |
| Timezone normalization | Join, leave, check-in, completion, and support timestamps compare correctly |
| Duplicate device or badge review | Shared device or proxy check-in risk is understood |
| Name variants | Arabic/English, initials, and spelling differences are resolved without creating duplicates |

### 3. Classify The Anomaly

Assign one primary anomaly class and any supporting tags:

- missing evidence;
- duplicate identity;
- duration below threshold;
- platform mismatch;
- manual override;
- late approval;
- withdrawal conflict;
- support-verified exception;
- policy ambiguity;
- suspected misuse.

### 4. Decide Eligibility Impact

| Decision | Use When | Required Evidence |
| --- | --- | --- |
| Eligible | Attendance and completion meet published criteria | Registration, attendance, and completion match |
| Eligible with exception | Policy allows exception and evidence supports it | Approved exception record and owner |
| Hold | Evidence is incomplete or contradictory | Open action and owner |
| Ineligible | Published criteria are not met | Clear reason and dispute route |
| Escalate | Misuse, identity conflict, complaint, or policy ambiguity | Governance or academic review record |

## Manual Override Controls

Manual attendance changes should require:

- participant reference;
- original value and new value;
- reason code;
- evidence reference;
- approving owner;
- timestamp;
- effect on Moodle provisioning and certificate eligibility;
- reviewer who did not create the original anomaly where feasible.

Avoid silent edits to attendance state after certificates are generated. Use correction or reissue workflows when eligibility changes after release.

## Participant Dispute Handling

When a participant disputes attendance or eligibility:

1. Verify identity through the approved support process.
2. Explain the published attendance requirement and evidence used.
3. Review support tickets, accessibility accommodations, platform outage records, and instructor notes.
4. Decide whether the issue is evidence missingness, policy exception, source-system error, or participant non-completion.
5. Record the final decision, appeal route, and any certificate release or hold action.

## Metrics

| Metric | Purpose |
| --- | --- |
| Missing attendance rate | Detects capture failures |
| Duplicate check-in rate | Identifies identity and proxy-attendance risk |
| Late join or early leave count | Supports policy clarity and session design |
| Manual override rate | Highlights governance drift |
| Exception approval rate | Reveals inconsistent application of attendance policy |
| Dispute reversal rate | Measures evidence quality and fairness |
| Moodle-attendance mismatch count | Detects integration or timezone issues |

## Closure Checklist

- Registration, verification, attendance, Moodle, payment, withdrawal, and support evidence were compared.
- Timestamps were normalized to the event timezone and UTC where needed.
- Duplicate identity and proxy check-in risks were reviewed.
- Manual overrides have reason, approver, and evidence reference.
- Eligibility decisions use published criteria or approved exception rules.
- Participant disputes include an explanation and appeal route.
- Attendance exports and working files follow the retention and privacy workflow.
