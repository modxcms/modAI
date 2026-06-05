# Contributing to modAI

Thank you for your interest in contributing to modAI.

modAI is the native AI framework for MODX Revolution. The goal is to build a shared foundation for AI-powered workflows throughout the MODX ecosystem.

Contributions of all sizes are welcome.

Useful contributions include:

- Bug fixes
- Documentation improvements
- Provider integrations
- Manager UI improvements
- Testing and QA
- Performance work
- Vision and image workflows
- Tools and agents (function calling)
- Vector search and retrieval

## Before you start

Please review:

- Open issues
- Existing pull requests
- Project discussions
- [DESIGN_PRINCIPLES.md](DESIGN_PRINCIPLES.md)
- [ARCHITECTURE.md](ARCHITECTURE.md)

For larger changes, open an issue or discussion before writing a full implementation.

That helps avoid duplicated work and keeps the change aligned with the project direction.

## Development workflow

1. Fork the repository.
2. Create a feature branch.
3. Make your changes.
4. Add or update tests when practical.
5. Update documentation when behavior changes.
6. Submit a pull request.

Use clear branch names.

Examples:

```text
fix/provider-error-handling
feature/gemini-vision-support
docs/provider-setup
```

## Local setup and tooling

Install dependencies:

```bash
composer install
npm install
```

Common commands:

```bash
npm run build      # build the Manager assets (esbuild)
npm run dev        # rebuild on change while developing
npm run lint       # lint JavaScript/TypeScript with ESLint
npm run docs:dev   # run the documentation site locally
```

PHP code follows the coding standard defined in `phpcs.xml` (PHP CodeSniffer). The project targets **PHP 8.2+** and **MODX Revolution 3.x**.

The user-facing documentation site lives in `_build/docs` (Docusaurus) and renders the Markdown in the top-level `docs/` directory.

## Pull requests

A good pull request should include:

- A clear description of the problem
- A clear description of the change
- Screenshots for UI changes
- Test notes or manual verification steps
- Links to related issues or discussions

Keep pull requests focused when possible.

A small, clear PR is easier to review than a large PR that mixes unrelated changes.

## Coding standards

Follow existing project patterns.

Contributions should:

- Avoid unnecessary dependencies
- Keep provider-specific logic in provider code
- Keep Manager UI code provider-neutral where practical
- Preserve backwards compatibility when practical
- Handle provider errors clearly
- Avoid exposing credentials or sensitive data

## Documentation

Update documentation when a change affects:

- Installation
- Configuration
- Supported providers
- Supported capabilities
- Developer APIs
- Manager behavior
- User-facing workflows

Documentation should be written for experienced MODX developers.

Use plain language. Avoid hype.

## Provider changes

Provider contributions should update [PROVIDERS.md](PROVIDERS.md).

Include:

- Supported capabilities
- Required credentials
- Model requirements
- Known limits
- Manual test notes

## UI changes

UI changes should include screenshots or screen recordings when practical.

Please check:

- Empty states
- Error states
- Loading states
- Long responses
- Small screens
- Permission-related behavior

## Security

Do not include API keys, secrets, access tokens, or real customer data in commits, issues, screenshots, or pull requests.

If you believe you have found a security issue, do not open a public issue. Contact the MODX team through the appropriate security channel.

## Questions

Use GitHub Discussions for architecture questions, provider ideas, and larger proposals.

Use GitHub Issues for bugs and clearly scoped tasks.

We appreciate your help building modAI.
