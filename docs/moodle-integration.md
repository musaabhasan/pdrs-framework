# Moodle Integration

PDRS integrates with Moodle through Moodle REST web services.

Official Moodle references:

- Moodle web services setup: https://docs.moodle.org/en/Using_web_services
- Moodle web service functions list: https://docs.moodle.org/dev/Web_services_Roadmap
- Moodle web service protocols: https://docs.moodle.org/dev/Webservice_protocols
- Moodle enrolment overview: https://docs.moodle.org/en/Enrolments

## Required Moodle Functions

The default client is designed around these functions:

```text
core_user_get_users_by_field
core_user_create_users
enrol_manual_enrol_users
core_cohort_add_cohort_members
```

The requested core provisioning functions are implemented:

```text
core_user_create_users
enrol_manual_enrol_users
```

User lookup is performed before account creation to prevent duplicates.

## Configuration

Set the following environment variables:

```text
MOODLE_BASE_URL=https://moodle.example.ac.ae
MOODLE_TOKEN=replace-with-least-privilege-service-token
MOODLE_REST_FORMAT=json
MOODLE_STUDENT_ROLE_ID=5
```

## Duplicate Identity Management

The registration workflow checks Moodle for existing users by:

1. Email address.
2. Generated username.

If a user exists, the platform reuses the Moodle user ID and proceeds to enrollment and cohort assignment.

## Field Mapping

Standard fields:

| PDRS field | Moodle field |
| --- | --- |
| `first_name` | `firstname` |
| `last_name` | `lastname` |
| `email` | `email` |
| `city` | `city` |

Custom event fields are mapped through each event's `custom_fields` JSON configuration:

```json
[
  {
    "name": "organization",
    "label": "Organization",
    "required": true,
    "moodle_shortname": "organization"
  }
]
```

## Course Enrollment

Each event can define one or more Moodle course IDs:

```json
[101, 102]
```

PDRS calls `enrol_manual_enrol_users` with the configured role ID.

## Cohort Assignment

Each event can define Moodle cohort IDs:

```json
[12]
```

PDRS calls `core_cohort_add_cohort_members` after user creation or lookup.

## Error Handling

Moodle failures mark the registration as `failed` and store a bounded failure reason. Production deployments should move Moodle provisioning into a queue so temporary API failures can be retried safely.
