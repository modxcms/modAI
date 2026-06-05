# Design principles

These principles guide product and architecture decisions for modAI.

## Native MODX experience

AI features should feel like part of MODX.

Users should be able to use AI where they already work: resources, fields, media, SEO tools, and Manager workflows.

Context switching should be rare. Configuration should be clear. The user experience should respect how MODX developers and editors already build sites.

## Provider agnostic

modAI should avoid hard dependence on any single AI vendor.

Providers change quickly. Models change even faster. Users have different budgets, policies, and data requirements.

The architecture should allow new providers and models to be added without rewriting the Manager experience.

## Capability driven

modAI should be organized around capabilities.

Examples:

- Chat
- Text generation
- Image generation
- Vision analysis
- Embeddings
- Vector search
- Function calling

Providers implement capabilities. Capabilities shape the user experience.

## Built for extension

Developers should be able to add providers, tools, capabilities, and integrations through clear extension points.

Special cases should be rare.

Core code should stay focused on shared behavior. Provider-specific code should live with the provider.

## Practical AI

modAI exists to help people get real work done.

Good features should reduce repetitive work, improve content quality, support accessibility, or make MODX workflows easier to manage.

AI novelty is not enough.

## Context matters

AI gets more useful when it has access to the right context.

Future work should help modAI understand the site it is assisting with. That may include:

- Resource discovery
- Site knowledge
- Embeddings
- Vector databases
- Retrieval-augmented generation
- Function calling

The user should stay in control of what context is available and how it is used.

## Progressive adoption

Users should be able to adopt modAI one workflow at a time.

A site may start with image alt text, then add SEO assistance, then content generation, then deeper assistant workflows.

The architecture should support that path without forcing a full rewrite of existing MODX processes.

## Shared foundation

modAI should provide common AI building blocks for the MODX ecosystem.

The long-term goal is to reduce duplicated work across future Extras, integrations, and Manager features.
