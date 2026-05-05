# Extension Guide

This guide explains where to extend the platform without weakening the existing security and maintainability boundaries.

## Extension Principles

- Keep controllers thin. Add business logic to services.
- Keep SQL inside repository classes.
- Store sensitive values encrypted and store searchable values as HMAC hashes.
- Add audit events for administrative changes, integration failures, and approval decisions.
- Add rate limits to any endpoint that sends email, checks credentials, or creates records.

## Adding Event Fields

Add fields in the event `custom_fields` JSON:

```json
[
  {
    "name": "organization",
    "label": "Organization",
    "type": "text",
    "required": true,
    "moodle_profile_field": "institution"
  }
]
```

Supported public form types are `text`, `email`, `tel`, `number`, `date`, `textarea`, and `select`. For `select`, add an `options` array.

`RegistrationService` stores the submitted metadata in encrypted form. `FieldMapper` maps configured values to Moodle custom profile fields.

## Adding Approval Rules

Extend `ApprovalService` when approval criteria become more advanced, such as:

- Seat capacity.
- Manager approval.
- Payment confirmation from a gateway.
- Registration windows.
- Organization-level eligibility rules.

Keep each rule deterministic and return an explicit status and reason.

## Adding an Admin Backend

Recommended modules:

- Event management.
- Registration review queue.
- Manual approval and rejection.
- Moodle provisioning retry screen.
- Audit log search.
- Export jobs.

Admin routes must require MFA, short sessions, CSRF protection, and role-based access control.

## Adding Integrations

Place integration clients in `src/Service` and keep credentials in environment variables or a secret manager. Never place tokens in seed data, docs, or source code.

For integrations that can fail, prefer retryable commands or queues rather than blocking user-facing registration flows for long-running operations.
