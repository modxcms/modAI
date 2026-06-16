# Roadmap

This roadmap describes current areas of work and likely future direction.

Many capabilities already ship today — including chat, text and image generation, vision, agents and tools (function calling), and retrieval via context providers. This roadmap focuses on deepening and expanding them.

Priorities may change as AI models, provider APIs, and MODX needs change.

## Foundation

Keep the core stable and easier to build on.

Examples:

- Provider improvements
- Better streaming support
- Clearer developer APIs
- Better configuration handling
- Better test coverage
- Clearer error handling

## Context-aware AI

Help modAI understand the site it is assisting with. Initial retrieval support ships today via context providers (including the built-in Pinecone provider); the goal is to broaden it.

Examples:

- More context provider integrations
- Improved embeddings and indexing
- Retrieval-augmented generation improvements
- Site-aware assistants
- Resource discovery
- Content relationship discovery

## Actions and automation

Allow AI to help perform approved work inside MODX. Function calling through agents and tools ships today; the goal is to expand the available actions and safeguards.

Examples:

- More built-in tools
- Resource draft creation
- Content update suggestions
- Metadata generation
- Approved external service integrations

AI-generated changes should be reviewable before they are applied.

## Content and media

Improve creative and editorial workflows.

A near-term focus is an **audio capability**, which improves accessibility and enables hands-free workflows:

- Text to audio — for example, reading an article aloud
- Voice input through speech to text — for example, dictating a prompt

Examples:

- Audio: text to audio and voice input (speech to text)
- Image generation improvements
- Vision analysis
- Alt text generation
- Caption generation
- Content refinement
- SEO metadata assistance
- Rich media workflows

## Provider ecosystem

Make it easier to add and maintain providers.

Examples:

- Shared provider interfaces
- Capability registration
- Provider test fixtures
- Provider documentation
- Community provider integrations

## Manager experience

Improve how AI appears inside MODX.

Examples:

- Better field-level actions
- Better chat workflows
- Clear model selection
- Clear error messages
- Better permission handling
- More useful defaults

## Longer-term direction

Potential long-term areas include:

- Skills: reusable, packaged units that bundle a prompt, tools, context, and instructions — an evolution of agents and the prompt library
- Sharing and installing skills across sites and the MODX community
- Site-aware assistants
- Agent-style workflows
- Retrieval tools
- Workflow automation
- Shared AI APIs for future Extras
- Deeper Manager integrations
