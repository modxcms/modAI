---
sidebar_position: 2
---

# Context Providers

## What a Context Provider Is
A **Context Provider** is a component that knows how to:
- Retrieve relevant documents or records (from MODX, a search index, or an external service).
- Convert them into chunks of text and/or metadata suitable for AI consumption.
- Optionally work with a vector database or other retrieval mechanism.

Agents call Context Providers when they need **grounded, site‑specific** information instead of relying only on the base model.

## Typical Data Sources
Examples of what a Context Provider might expose:
- MODX **Resources** (e.g., content pages, knowledge base articles)
- MODX **Elements** (Snippets, Chunks, Templates) and their descriptions
- Indexed documentation, FAQs, or release notes
- External knowledge bases or search APIs

## Registering Context Providers
To add a new provider:
- Implement a PHP class that follows the `\modAI\ContextProviders\ContextProviderInterface` interface.
- Register the context provider with modAI:
    - Create a plugin that will run on `modAIOnContextProviderRegister` event and will return the class name of the Context Provider or an array of multiple context providers you wish to register.
- Create the context provider from Context Providers tab:
    - Select the Context Provider class
    - A **unique name**.
    - Set the internal description explaining what the Context Provider does

Once registered, the context provider appears in the **Context Provider** tab and you’ll be able to attach it to agents.

### The `ContextProviderInterface`

A custom provider implements `\modAI\ContextProviders\ContextProviderInterface`:

```php
public function __construct(modX $modx, array $config = []);

// Return relevant context strings for a given prompt (this is the retrieval step).
public function provideContext(string $prompt): array;

// Admin-configurable options (rendered as a config form via ConfigBuilder).
public static function getConfig(modX $modx): array;

// Internal description shown to admins.
public static function getDescription(): string;

// The default provider name, pre-filled when configuring.
public static function getSuggestedName(): string;
```

Throw `\modAI\Exceptions\InvalidContextProviderConfig` from the constructor when required config is missing — modAI catches it and shows a friendly error.

:::note
`provideContext()` is the only method the interface requires for retrieval. Indexing methods (`index()` / `delete()`) are **not** part of the interface — the built-in Pinecone provider adds them so it can sync MODX content into the vector database (see below).
:::

## Built-in Provider: Pinecone

modAI ships a built-in **Pinecone** provider (`\modAI\ContextProviders\Pinecone`) for retrieval-augmented generation against a [Pinecone](https://www.pinecone.io/) vector index. The class is registered for selection out of the box, but **no provider instance is created automatically** — you configure one yourself.

### Setup

1. From the **Context Providers** tab, create a provider and choose the **Pinecone** class (the name pre-fills as `pinecone`).
2. Fill in the configuration (at minimum `api_key`, `endpoint`, and `namespace`).
3. **Enable** the provider and **attach it to an Agent**. A provider is only queried when it is enabled *and* linked to the agent in use.

:::tip
Any config value can be stored in a MODX system setting instead of inline by prefixing it with `ss:` — for example, set `api_key` to `ss:modai.pinecone_key` to read the secret from the `modai.pinecone_key` system setting.
:::

### Configuration

| Key | Required | Default | Purpose |
| --- | --- | --- | --- |
| `api_key` | ✅ | – | Pinecone API key (sent as the `Api-Key` header). |
| `endpoint` | ✅ | – | Your Pinecone index host URL (used as the API base URL). |
| `namespace` | ✅ | – | Pinecone namespace to read from and write to. |
| `api_version` | | `2025-04` | Pinecone API version (`X-Pinecone-API-Version` header). |
| `fields` | | – | Comma-separated list of fields to index (which data gets stored and embedded). |
| `fields_map` | | – | Comma-separated `original:new` pairs to rename fields on upsert. |
| `id_field` | | – | Field used to format the record ID in output (supports `{field}` and `{++system_setting}` placeholders). |
| `output_fields` | | – | Comma-separated list of result fields to include in the returned context. |
| `context_messages` | | – | Templated lines prepended to each result. One per line; supports `{id}`, `{field}`, and `{++system_setting}` placeholders. |

### How retrieval works

When an agent runs, modAI sends the prompt to Pinecone using its **integrated embedding** (Pinecone embeds the query server-side — modAI does not compute vectors itself). It retrieves the top 5 candidates, then uses Pinecone's **integrated reranking** (`bge-reranker-v2-m3`) to narrow them to the top 3. Each result is formatted into a context string using your `context_messages` and `output_fields` settings and merged into the agent's context.

### How indexing works

The Pinecone provider can automatically **index your MODX content** as it changes — resources, chunks, snippets, and templates are upserted on save and removed on delete (via the core `OnDocFormSave`, `OnChunkSave`, `OnSnippetSave`, `OnTemplateSave` events and their delete counterparts). Each record is stored under an ID like `resource_42`, with a `text` field that Pinecone embeds.

To enable auto-indexing, point each entity type at your provider using the `contexts` system settings — set them to the **name** of your Pinecone provider:

| Setting | Indexes |
| --- | --- |
| `modai.contexts.resources.name` | Resources |
| `modai.contexts.chunks.name` | Chunks |
| `modai.contexts.snippets.name` | Snippets |
| `modai.contexts.templates.name` | Templates |

Leave a setting empty to skip auto-indexing for that type. The provider's `fields` option controls which data is actually stored and embedded.

## Configuring Context Providers in the Manager
From the **Context Providers** tab you can:
- Enable or disable providers.
- Attach providers to specific **Agents** so only the right agents can access a given data source.

Design tips:
- Use separate providers for very different datasets (e.g., “Public Docs”, “Internal Runbooks”, “Developer API Docs”).
- Keep provider scopes narrow so retrieval stays relevant and efficient.

## Performance and Cost
Context providers may rely on:
- Full‑text search queries
- Vector database lookups
- External API calls

Be mindful of:
- Token usage when passing large chunks of context into prompts.
- Rate limits and pricing for external services.
- Caching strategies to reduce repeated work.
