# Professional Development Registration System Framework

PDRS is a PHP 8.x and MySQL 8.0 framework for secure professional development registration, identity verification, and Moodle enrollment automation.

It is designed for education and enterprise environments where registration workflows must balance user experience, security, auditability, privacy, and operational maintainability.

## What It Provides

- Dynamic event landing pages using event slugs such as `/e/secure-ai-governance`.
- Flexible program delivery metadata for synchronous, asynchronous, self-paced, instructor-led, hybrid, cohort-based, and custom formats.
- Event-specific metadata fields mapped to Moodle custom profile fields.
- Optional invite-code gates per event, with only HMAC hashes stored in the database.
- Mandatory email verification before registration records are created.
- OTP and signed-link verification workflows.
- Session-backed CSRF protection across public form submissions.
- Duplicate identity checks against Moodle before account creation.
- Duplicate registration handling by event and verified email.
- Moodle REST integration for user creation, cohort assignment, and course enrollment.
- Retryable Moodle provisioning utility for operational recovery.
- Automatic approval based on domain allow-lists and payment status flags.
- AES-256-GCM encryption for sensitive registration data at rest.
- HMAC hashing for email, IP address, and user-agent lookups without exposing raw values.
- PDO prepared statements for all database operations.
- Rate limiting for verification endpoints.
- Audit logging for registration attempts, verification events, administrative changes, and integration failures.
- Liveness, readiness, and protected operations metrics endpoints.
- Maintenance commands for expired verification, rate-limit, and audit-log cleanup.
- SMTP transactional email transport with local mail logging for development.
- Trusted proxy configuration for accurate client IP handling behind load balancers.
- Rich field rendering and validation for text, email, number, date, textarea, and select inputs.
- Dockerized local development with PHP 8.3, Apache, MySQL 8.0, and Mailpit.

## Architecture

```text
Registrant
  -> Event landing page
  -> Email OTP or signed-link verification
  -> Registration form
  -> Approval policy
  -> Moodle identity check
  -> User creation if needed
  -> Cohort assignment and course enrollment
  -> Confirmation email and calendar invite
```

## Quick Start

```bash
cp .env.example .env
docker compose up --build
```

Then open:

- Application: `http://localhost:8080`
- Demo event: `http://localhost:8080/e/secure-ai-governance`
- Mailpit inbox: `http://localhost:8025`

Generate a strong local `APP_KEY` before testing encryption:

```bash
php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
```

If PHP is not installed locally, run that command inside a PHP container or replace the key with any secure 32-byte base64 value.

The example key in `.env.example` is only for local development and must be replaced before production use.

## Operational Commands

Run scheduled maintenance:

```bash
php bin/maintenance.php
```

Retry approved or failed Moodle provisioning records:

```bash
php bin/retry-provisioning.php
```

Generate an operations bearer token hash:

```bash
php -r "echo hash('sha256', 'replace-with-strong-token') . PHP_EOL;"
```

Set the result as `OPERATIONS_TOKEN_HASH` and call `/ops/metrics` with `Authorization: Bearer <token>`.

Generate an invite-code hash for an invite-only event:

```bash
php bin/hash-invite-code.php "program-invite-code"
```

Set the generated value in `events.invite_code_hash` after the production `APP_KEY` has been configured.

## Documentation

- [Architecture](docs/architecture.md)
- [Security and Data Protection](docs/security.md)
- [Security Controls](docs/security-controls.md)
- [Secure Enrollment Threat Model](docs/secure-enrollment-threat-model.md)
- [Moodle Integration](docs/moodle-integration.md)
- [Database Schema](docs/database.md)
- [Development Guide](docs/development.md)
- [Operations Guide](docs/operations.md)
- [API and Routes](docs/api.md)
- [Extension Guide](docs/extension-guide.md)
- [Production Checklist](docs/production-checklist.md)
- [Testing Guide](docs/testing.md)
- [Future Roadmap](docs/roadmap.md)

## Repository Structure

```text
public/                 Web entry point and assets
src/                    Application code
src/Http                Request, response, and router
src/Repository          PDO database access layer
src/Service             Business services and integrations
database/migrations     MySQL schema and demo event seed
database/seeders        Optional standalone seed data
docs/                   Implementation and operations documentation
bin/                    Utility scripts
```

## Production Notes

Deploy the application and database to approved infrastructure for the institution, enforce TLS at the load balancer or reverse proxy layer, keep Moodle tokens in a secret manager, and restrict outbound access to approved Moodle endpoints and SMTP infrastructure.

## License

MIT License. See [LICENSE](LICENSE).

<!-- portfolio:start -->
## Portfolio and Professional Profile

This repository is part of the professional portfolio of [Musaab Hasan](https://musaab.info), focused on cybersecurity, digital forensics, AI governance, EdTech, secure platforms, and research-driven digital transformation.

### Digital Forensics and Security Research Labs

- [Android Digital Forensics Lab](https://github.com/musaabhasan/android-forensics-lab) - Advanced Android forensics workbench for acquisition planning, anti-forensics evaluation, memory triage, evidence integrity, and case reconstruction.
- [Humanoid Robot Forensics Lab](https://github.com/musaabhasan/humanoid-robot-forensics-lab) - PHP/MySQL forensic casework platform for humanoid robot, companion app, and IoT evidence triage.
- [Smart Metering Security Lab](https://github.com/musaabhasan/smart-metering-security-lab) - Research portal based on smart metering security analysis for cyber-physical and smart-grid environments.
- [Drive-by Download ML Lab](https://github.com/musaabhasan/driveby-download-ml-lab) - Machine learning research portal for detecting drive-by download attacks and web-based malware delivery.
- [SQL Injection ML Detection Lab](https://github.com/musaabhasan/sqli-ml-detection-lab) - Research portal for SQL injection detection using machine learning and security telemetry.
- [IoT Board SSH Hardening Lab](https://github.com/musaabhasan/iot-board-ssh-hardening-lab) - SSH exposure assessment and hardening portal for IoT development boards and embedded Linux systems.
- [ZigBee WHAS Design Lab](https://github.com/musaabhasan/zigbee-whas-design-lab) - Research portal for designing and evaluating ZigBee wireless home automation systems.
- [Mammogram Fourier Analysis Lab](https://github.com/musaabhasan/mammogram-fourier-analysis-lab) - Medical image-processing research portal based on Fourier transform analysis for mammography.

### Security Culture and Transformation Platforms

- [Human Factors Risk Profiler](https://github.com/musaabhasan/human-factors-risk-profiler) - Human-centered security risk profiling portal for targeted interventions and behavior-aware controls.
- [Security Champion Network Portal](https://github.com/musaabhasan/security-champion-network-portal) - Platform for managing security champion networks, missions, recognition, and measurable impact.
- [Crisis Simulation Command Portal](https://github.com/musaabhasan/crisis-simulation-command-portal) - Cyber crisis simulation planning, scoring, and improvement platform for resilience exercises.
- [Behavioral Security Metrics Portal](https://github.com/musaabhasan/behavioral-security-metrics-portal) - Evidence-based security awareness metrics portal focused on behavior, culture, and intervention outcomes.
- [Security Culture Heatmap Portal](https://github.com/musaabhasan/security-culture-heatmap-portal) - Security culture maturity heatmap for norms, leadership signals, and organizational readiness.
- [Emerging Technology Security Culture Portal](https://github.com/musaabhasan/emerging-technology-security-culture-portal) - Adoption-readiness portal for emerging technology, governance, and security culture alignment.
- [AI Use Case Evaluation Portal](https://github.com/musaabhasan/ai-use-case-evaluation-portal) - Evaluation platform for AI use cases across value, feasibility, data readiness, privacy, ethics, and governance.
- [Transformation Roadmap Portal](https://github.com/musaabhasan/transformation-roadmap-portal) - Roadmap platform for moving security culture programs from compliance orientation to resilience and measurable change.

### Governance, Education, and Secure Enablement

- [Professional Development Registration System Framework](https://github.com/musaabhasan/pdrs-framework) - Secure registration and Moodle enrollment automation framework for professional development programs.
- [Multilingual Certificate Issuer](https://github.com/musaabhasan/multilingual-certificate-issuer) - Arabic/English certificate design, PDF generation, and throttled SMTP distribution platform.
- [AI Security Governance Toolkit](https://github.com/musaabhasan/ai-security-governance-toolkit) - Practical AI security governance controls, templates, evidence registers, playbooks, and policy-as-code examples.

Professional profile and research portfolio: [https://musaab.info](https://musaab.info)
<!-- portfolio:end -->
