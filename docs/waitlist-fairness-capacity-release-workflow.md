# Waitlist Fairness And Capacity Release Workflow

This workflow defines how administrators release seats from a waitlist when a professional development event reaches capacity. It is designed for education and enterprise programs where limited seats, sponsor allocations, accessibility needs, invite-only cohorts, cancellations, and Moodle provisioning create fairness and auditability risks.

## Objectives

- Apply transparent priority rules before releasing seats.
- Prevent hidden preference, duplicate registration, and sponsor over-allocation.
- Keep accessibility accommodations and approved exceptions visible in the release decision.
- Avoid overbooking unless the risk is explicitly approved.
- Expire seat offers consistently and return unused seats to the waitlist.
- Preserve evidence for complaints, audit reviews, and certificate eligibility disputes.

## Release Inputs

| Input | Evidence |
| --- | --- |
| Event capacity | Approved capacity, venue or virtual platform limit, instructor limit, Moodle course capacity |
| Reserved seats | Sponsor allocation, internal seats, accessibility reserve, instructor-approved holdback |
| Current confirmed registrations | Approved registrations, cancellations, withdrawals, duplicate cleanup |
| Waitlist queue | Timestamp, verification state, priority group, sponsor, accessibility flag, exception status |
| Provisioning status | Moodle account, cohort, course enrollment, retry state |
| Certificate impact | Whether late admission can still meet attendance, completion, or assessment requirements |

## Priority Rules

Define priority before opening registration or before the first release wave.

| Rule | Example |
| --- | --- |
| First verified waitlist timestamp | Earlier verified registrants are invited first |
| Sponsor allocation | Seats released within each sponsor quota before general pool release |
| Eligibility group | Required cohort, job role, department, or prerequisite completion |
| Accessibility accommodation | Accommodation-linked seats reviewed before general release |
| Manual exception | Approved identity, payment, or sponsor correction exception |
| Certificate viability | Seat is not released if the registrant can no longer satisfy completion requirements |

## Release Wave Steps

1. Freeze the waitlist snapshot with timestamp, event ID, capacity, reserved seats, and current confirmed count.
2. Remove ineligible, duplicate, unverified, withdrawn, or expired records from the candidate set.
3. Recalculate open seats after cancellations, sponsor holds, accessibility reserves, and no-show policy.
4. Sort candidates by approved priority rules and preserve the sorted queue as evidence.
5. Select the release wave size and confirm whether overbooking is prohibited, allowed, or conditionally approved.
6. Send seat offers with a clear expiry deadline, accepted action, support route, and privacy-minimized message.
7. Lock offered seats until the deadline expires or the invite is declined.
8. Convert accepted offers into approved registrations and trigger Moodle provisioning.
9. Return expired or declined seats to the next release wave.
10. Reconcile final registrations, provisioning, attendance eligibility, and certificate impact before closing the release wave.

## Overbooking Approval

Overbooking should be exceptional and evidence-based.

| Question | Required Answer |
| --- | --- |
| What historical no-show or cancellation rate supports overbooking? |  |
| What is the maximum safe overbooking count? |  |
| Who approves overbooking for this event? |  |
| What happens if all overbooked participants attend? |  |
| Does overbooking affect accessibility accommodations, instructor workload, venue safety, or certificate quality? |  |

## Invite Expiry Handling

| Condition | Action |
| --- | --- |
| Offer accepted before expiry | Approve registration and trigger provisioning |
| Offer not accepted by expiry | Mark invite expired and release seat in next wave |
| Recipient reports email delivery failure | Verify support evidence and decide whether to reissue offer |
| Duplicate account blocks acceptance | Route to identity proofing exception workflow |
| Sponsor changes eligibility | Route to sponsor correction or roster validation workflow |
| Accessibility accommodation needs review | Hold seat until accommodation decision is recorded |

## Audit Evidence

| Evidence | Purpose |
| --- | --- |
| Frozen waitlist snapshot | Proves candidate order at release time |
| Priority rule configuration | Shows the release method was pre-defined |
| Capacity calculation | Shows available seats and reserved seats |
| Offer message template | Shows consistent recipient communication |
| Offer sent, opened, accepted, expired, or declined timestamps | Supports dispute handling |
| Moodle provisioning record | Confirms downstream enrollment |
| Exception approvals | Explains deviations from queue order |
| Final reconciliation | Confirms seats, registrations, and certificate eligibility align |

## Complaint Review

When a registrant disputes a waitlist outcome:

- Review the frozen snapshot instead of the current mutable queue.
- Compare the complainant with candidates selected in the same wave.
- Check whether sponsor quota, eligibility, accessibility, or manual exception rules changed priority.
- Confirm the offer was sent to a verified address and was not suppressed or bounced.
- Confirm the expiry deadline was applied consistently.
- Provide privacy-safe reasoning without exposing other participants' personal details.

## Closure Checklist

- Waitlist snapshot and priority rules are archived.
- Release-wave size and capacity calculation are documented.
- Reserved seats and sponsor allocations reconcile.
- Expired and declined offers are handled consistently.
- Exceptions are linked to approval evidence.
- Moodle provisioning is complete or queued for retry.
- Certificate eligibility impact is assessed for late admissions.
- Complaint-ready evidence is retained according to the data retention workflow.
