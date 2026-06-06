# modAI

The native AI framework for MODX Revolution.

modAI brings AI tools into the MODX Manager for content generation, image generation, image analysis, chat, agents and tools, provider integrations, and AI-assisted workflows across the MODX ecosystem.

The project is built by the MODX team and is intended to grow with MODX itself.

## Why modAI exists

Many CMS AI integrations wrap a single API and expose a few prompt buttons.

modAI takes a different path. It gives MODX a shared AI foundation that supports multiple providers, multiple model types, agents and tools, retrieval, and deeper Manager workflows.

The goal is practical: help MODX users create, refine, review, and manage content with AI assistance inside the tools they already use.

## Project philosophy

modAI is more than an AI content generator.

The long-term goal is a shared AI layer for the MODX ecosystem. That includes content generation, image workflows, contextual assistance, automation, and future AI-powered experiences built on a common architecture.

Read [DESIGN_PRINCIPLES.md](DESIGN_PRINCIPLES.md) before proposing large changes.

## Project status

modAI is actively developed and used in production.

Current capabilities include:

- AI chat and assistant workflows
- Text generation
- Image generation
- Vision analysis
- Multi-provider support
- Streaming responses
- Agents, tools, and function calling
- Vector search and RAG via Context Providers

Community feedback, testing, bug reports, and contributions are welcome.

## Features

### AI chat

Use AI models directly within the MODX Manager through a streaming chat interface, with persistent history and optional shared chats.

### Text generation

Generate, rewrite, summarize, and improve content without leaving MODX.

### Image generation

Create original images with supported AI providers and add them to your content workflow, including directly from the Media browser.

### Vision analysis

Analyze images and generate alt text, captions, summaries, and descriptions.

### SEO assistance

Generate titles, meta descriptions, summaries, and other SEO-focused content.

### Agents, tools, and function calling

Configure agents that bundle a model, a system prompt, tools, and context providers. modAI ships with built-in tools for working with MODX resources, templates, chunks, and categories, and you can register your own.

### Vector search and RAG

Ground responses in your own content through Context Providers. A built-in Pinecone provider supports retrieval and can automatically index MODX content.

### Multi-provider architecture

Use the AI models that fit your workflow, requirements, and budget.

Supported providers currently include:

- OpenAI
- Anthropic Claude
- Google Gemini
- OpenRouter
- Custom / OpenAI-compatible endpoints

More providers can be added through the provider layer.

## Documentation

User and configuration documentation lives on the documentation site:

- **Docs site:** https://modxcms.github.io/modAI/
- **Extras package:** https://extras.modx.com/package/modai

Developer and contributor documentation:

| Document | Purpose |
| --- | --- |
| [DESIGN_PRINCIPLES.md](DESIGN_PRINCIPLES.md) | Product and architecture principles |
| [ARCHITECTURE.md](ARCHITECTURE.md) | System architecture and component design |
| [PROVIDERS.md](PROVIDERS.md) | Provider support and provider integration guidance |
| [ROADMAP.md](ROADMAP.md) | Current priorities and future direction |
| [CONTRIBUTING.md](CONTRIBUTING.md) | Contribution guidelines and development workflow |

If you want to contribute, start with the design principles and architecture docs.

## Requirements

- MODX Revolution 3.x
- PHP 8.2+
- Credentials for at least one supported AI provider

Provider-specific features may have their own requirements.

## Installation

Install modAI through the MODX Extras installer ([package page](https://extras.modx.com/package/modai)). The installer prompts you for provider API keys as part of setup.

### Getting provider API keys

You need **at least one** provider key to use modAI. The installer prompts you for keys during setup, and you can add or change them later in the system settings (`modai.api.{service}.key`).

Create a key with the provider(s) you want to use:

| Provider | Get a key | Notes |
| --- | --- | --- |
| **OpenAI** (ChatGPT, image generation) | [platform.openai.com/api-keys](https://platform.openai.com/api-keys) | Requires a billing method / prepaid credits. The API is billed separately from any ChatGPT subscription. |
| **Anthropic** (Claude) | [platform.claude.com/settings/keys](https://platform.claude.com/settings/keys) | Created in the Claude Console; requires account credits/billing. |
| **Google Gemini** | [aistudio.google.com/apikey](https://aistudio.google.com/apikey) | Created in Google AI Studio; free tier available with limits (higher usage and image generation may require billing). |
| **OpenRouter** (multi-vendor gateway) | [openrouter.ai/settings/keys](https://openrouter.ai/settings/keys) | One key for many models and providers; requires account credits. |

Custom / OpenAI-compatible endpoints are configured directly in the system settings. Treat API keys like passwords — never commit or share them.

See [PROVIDERS.md](PROVIDERS.md) for provider setup notes.

### After installation

1. Enable the features you want to use.
2. Start using AI features inside the MODX Manager.

## Development setup

Clone the repository:

```bash
git clone https://github.com/modxcms/modAI.git
cd modAI
```

Install dependencies:

```bash
composer install
npm install
```

Build assets:

```bash
npm run build
```

Start development mode (watch/rebuild):

```bash
npm run dev
```

See [ARCHITECTURE.md](ARCHITECTURE.md) for implementation notes and [CONTRIBUTING.md](CONTRIBUTING.md) for the full workflow.

## Contributing

We welcome contributions of all sizes.

Useful areas include:

- Provider integrations
- Manager UI improvements
- Documentation
- Testing and QA
- Performance work
- Vision workflows
- Tools and agents
- Vector search and retrieval

Before starting a larger change, review:

- [DESIGN_PRINCIPLES.md](DESIGN_PRINCIPLES.md)
- [ARCHITECTURE.md](ARCHITECTURE.md)
- [CONTRIBUTING.md](CONTRIBUTING.md)

For significant changes, open an issue or discussion first.

## Community

- Report bugs through GitHub Issues
- Discuss ideas through GitHub Discussions
- Share feedback with the MODX community

## License

modAI is open source software released under the terms of the included [license](LICENSE.md) (GNU AGPL v3.0).
