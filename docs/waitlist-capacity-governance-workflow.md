# Waitlist And Capacity Governance Workflow

This workflow defines how event capacity, waitlist ordering, invite release waves, overbooking, accessibility accommodations, and audit evidence should be managed for professional development programs. It is intended for cohorts, workshops, certification events, and Moodle-linked registrations where fairness and traceability matter.

## Governance Goals

- Prevent registration beyond approved room, instructor, license, platform, or support capacity.
- Treat waitlisted participants consistently using documented priority rules.
- Preserve evidence for invite-code releases, manual overrides, cancellations, no-shows, and accommodation seats.
- Avoid silent exclusion of eligible participants when a capacity limit changes.
- Support certificate eligibility decisions by keeping enrollment state and attendance evidence aligned.

## Capacity Model

| Capacity Type | Definition | Owner |
| --- | --- | --- |
| Physical capacity | Seats available in the room after safety and accessibility requirements | Event operations |
| Virtual capacity | Platform seats, breakout room capacity, streaming licenses, or proctoring limits | Technology owner |
| Instructor capacity | Maximum participant load for facilitation, grading, feedback, or lab support | Academic owner |
| Moodle capacity | Course, cohort, group, or license constraints that affect provisioning | Moodle administrator |
| Support capacity | Help desk, accessibility, translation, or assessment support availability | Program owner |
| Reserved capacity | Seats intentionally held for partners, accessibility needs, late approvals, or waitlist releases | Program owner |

Use the most restrictive capacity as the effective registration limit unless governance explicitly approves a different limit.

## Required Data Fields

| Field | Purpose |
| --- | --- |
| Event ID and slug | Links waitlist decisions to the public event page |
| Effective capacity | Approved participant limit after all constraints |
| Reserved seats | Seats held for policy-based reasons |
| Waitlist order timestamp | Provides deterministic ordering |
| Priority category | Sponsor, department, accessibility, prerequisite, payment, or open registration |
| Invitation wave | Tracks when waitlisted users were invited |
| Response deadline | Defines when an invite expires |
| Override reason | Required for manual movement, overbooking, or exception release |
| Evidence reference | Audit trail for decisions and approvals |

## Registration State Model

| State | Meaning | Allowed Next States |
| --- | --- | --- |
| Pending verification | Email or identity verification not completed | Verified, expired |
| Verified pending approval | User verified but approval or capacity decision is pending | Confirmed, waitlisted, rejected |
| Confirmed | Seat assigned and participant can be provisioned to Moodle | Cancelled, withdrawn, attended, no-show |
| Waitlisted | User is eligible but no seat is currently available | Invited, rejected, withdrawn |
| Invited from waitlist | Seat is temporarily held while user responds | Confirmed, expired, withdrawn |
| Expired invite | Invite deadline passed without confirmation | Waitlisted, rejected |
| Cancelled or withdrawn | Seat released by user, administrator, or policy | Closed, waitlisted replacement invited |
| No-show | Confirmed participant did not attend | Closed, certificate ineligible unless exception approved |

## Waitlist Ordering Rules

1. Apply eligibility gates before ordering, including prerequisites, payment status, domain rules, and verification state.
2. Rank participants by documented priority category only when the event policy allows it.
3. Use verified waitlist timestamp as the default tie-breaker.
4. Keep accessibility accommodation requests out of public rankings and handle them through controlled reserved capacity.
5. Do not reorder waitlisted participants manually without an override reason and approver.
6. Preserve expired invitations in the audit trail instead of deleting them.
7. Record whether a participant declined, failed to respond, was unreachable, or was moved by policy.

## Invite Release Waves

| Step | Control |
| --- | --- |
| Calculate available seats | Confirm cancellations, capacity increases, reserved-seat release, and Moodle provisioning limits |
| Select candidates | Apply priority rules and waitlist timestamp ordering |
| Hold seats | Reserve a seat until the response deadline |
| Send invite | Include event, deadline, accessibility contact, and consequences of non-response |
| Monitor responses | Confirm accepted seats and expire non-responses automatically |
| Reconcile Moodle | Provision confirmed users and remove expired or withdrawn users |
| Close evidence | Save selected candidates, skipped candidates, message IDs, and outcome counts |

## Overbooking Governance

Overbooking should be exceptional and evidence-based. Require approval when:

- effective capacity would be exceeded;
- the event depends on limited licenses, lab accounts, assessment seats, or instructor feedback;
- safety, accessibility, or room constraints may be affected;
- overbooking could reduce participant support quality;
- certificate eligibility depends on attendance verification that may become unreliable.

Document the reason, approving owner, extra capacity source, risk accepted, and rollback plan if attendance exceeds the workable limit.

## Accessibility And Accommodation Controls

- Provide a private channel for accessibility and accommodation requests.
- Hold reserved capacity where required by policy or event design.
- Do not expose accommodation status in public waitlist exports.
- Track only the minimum necessary accommodation evidence in the registration system.
- Confirm support arrangements before releasing a reserved seat to the general waitlist.
- Preserve audit evidence that accommodation requests were handled without unfair delay.

## Cancellation And No-Show Handling

| Scenario | Required Action |
| --- | --- |
| Early cancellation | Release seat to next eligible waitlisted participant |
| Late cancellation | Decide whether there is enough time to invite a replacement |
| No-show | Mark attendance state and certificate eligibility impact |
| Repeated no-show | Apply policy only if disclosed before registration |
| Capacity reduction | Notify affected participants and record selection criteria |
| Capacity increase | Release seats through the documented waitlist process |

## Audit Evidence

Retain the following evidence for each high-demand event:

- approved capacity and reservation rationale;
- waitlist snapshot before each invite wave;
- priority rule configuration and eligibility filters;
- invite message IDs and response deadlines;
- expired, declined, withdrawn, and accepted outcomes;
- manual overrides with approver and reason;
- Moodle provisioning reconciliation after each wave;
- attendance and certificate eligibility impacts.

## Metrics

| Metric | Use |
| --- | --- |
| Fill rate | Shows whether capacity was used efficiently |
| Waitlist conversion rate | Measures how many waitlisted users accepted invitations |
| Average wait time by priority category | Detects fairness or process issues |
| Expired invite rate | Indicates whether deadlines or communication need adjustment |
| Manual override count | Highlights governance drift |
| Overbooking frequency | Tracks operational risk |
| No-show rate | Supports capacity planning and certificate eligibility controls |
| Accessibility response time | Measures support quality without exposing private details |

## Closure Checklist

- Effective capacity and reserved-seat logic are documented.
- Waitlist ordering and invite waves are reproducible from retained evidence.
- Manual overrides have approver, reason, timestamp, and affected participant reference.
- Moodle enrollment matches confirmed participant state.
- Attendance and certificate eligibility records reflect cancellations, withdrawals, and no-shows.
- Privacy review confirms that waitlist and accommodation data are retained only as long as needed.
