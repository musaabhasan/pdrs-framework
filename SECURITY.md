# Security Policy

## Supported Versions

The main branch is the active development line for this framework.

## Reporting Security Issues

Report suspected vulnerabilities privately to the repository maintainer. Include:

- A clear description of the issue.
- Steps to reproduce.
- Affected route, component, or configuration.
- Potential impact.
- Suggested remediation, if known.

Do not publish exploit details until the issue has been assessed and a fix is available.

## Security Baseline

- PHP 8.2 or newer.
- MySQL 8.0 or newer.
- AES-256-GCM encryption for sensitive registration data.
- HMAC lookup fields for email, IP address, and user-agent.
- CSRF protection for public POST routes.
- Rate limiting for verification and registration actions.
- Least-privilege Moodle API token.
- Audit logging for registration and integration events.
