# Contributing

## Development Workflow

1. Create a focused branch.
2. Keep controllers thin and place business logic in services.
3. Keep SQL in repositories.
4. Update documentation when behavior, configuration, or operations change.
5. Run quality checks before opening a pull request.

## Quality Checks

```bash
php bin/lint.php
php bin/self-test.php
php bin/test.php
```

## Coding Standards

- Use strict types for PHP files.
- Prefer constructor injection for services and repositories.
- Avoid storing raw personal data when encrypted or hashed alternatives are available.
- Do not log secrets, tokens, OTP values, or raw personal data.
- Keep public-facing text clear, professional, and implementation-ready.

## Pull Request Expectations

- Describe the user or operational problem solved.
- List database changes, if any.
- List new environment variables, if any.
- Include test notes.
- Include rollback considerations for production-impacting changes.
