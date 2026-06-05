# Architecture

modAI is the native AI framework for MODX Revolution.

It provides a common structure for AI capabilities, provider integrations, Manager workflows, agents and tools, and retrieval across MODX.

## Architecture overview

```text
Manager UI (_build/js)
    |
    v
API / executor (src/API)
    |
    |-- Prompt endpoints: Text, Vision, Image, Chat
    |-- Chat history: load, store, pin, search, share
    |-- Tools/Run, Context/Get, Download
    |
    v
Capability layer
    |
    |-- Chat / text generation
    |-- Image generation
    |-- Vision analysis
    |-- Agents, tools, function calling
    |-- Context providers (RAG)
    |
    v
Provider layer (src/Services)
    |
    |-- OpenAI
    |-- Anthropic Claude
    |-- Google Gemini
    |-- OpenRouter
    |-- Custom (OpenAI-compatible)
    |
    v
External AI services
```

## Core idea

modAI separates user-facing capabilities from provider-specific implementations.

The Manager should not need to know which provider is generating text, analyzing an image, or returning a chat response. It calls a capability; the provider layer handles the provider-specific request and response details.

Providers build a request description (an `AIResponse`) rather than calling the HTTP API directly — modAI's executor performs the actual request. This keeps the user experience consistent while allowing providers and models to change over time.

## Codebase map

All PHP lives under `core/components/modai/src/`. Front-end code lives under `_build/js/src/`.

| Area | Location | Key types |
| --- | --- | --- |
| Provider layer | `src/Services/` | `AIService` (interface), `AIServiceFactory`, `Response/AIResponse`, `ApiKey` (trait), `Config/{CompletionsConfig,VisionConfig,ImageConfig}`, and the providers `OpenAI` / `Anthropic` / `Google` / `OpenRouter` / `CustomOpenAI` |
| Request executor | `src/API/API.php` | Executes an `AIResponse`; honors `modai.api.*.execute_on_server` |
| API endpoints | `src/API/` | `Prompt/{Text,Vision,Image,Chat,ChatTitle,AdditionalOptions}`, `Chat/*` (history), `Tools/Run`, `Context/Get`, `Download/*` |
| Tools | `src/Tools/` | `ToolInterface` + built-in tools (resources, templates, chunks, categories) |
| Context providers | `src/ContextProviders/` | `ContextProviderInterface`, built-in `Pinecone` |
| Admin config forms | `src/Config/` | `ConfigBuilder`, `FieldBuilder` (shared by tools and context providers) |
| Data models | `src/Model/` | `Agent`, `Tool`, `AgentTool`, `ContextProvider`, `AgentContextProvider`, `PromptLibraryCategory`, `PromptLibraryPrompt`, `Chat`, `Message` |
| Manager UI | `_build/js/src/` | Chat modal, global button, media-browser integration, executor, provider response handlers |
| Settings | `src/Settings.php`, `_build/gpm.yaml` | Namespaced `modai.*` system settings |

## Extension points

modAI is extended through MODX plugin events. A plugin returns one or more class names from the relevant event:

- **`modAIOnServiceRegister`** — register a custom AI service (implements `AIService`).
- **`modAIOnToolRegister`** — register a custom tool (implements `ToolInterface`).
- **`modAIOnContextProviderRegister`** — register a custom context provider (implements `ContextProviderInterface`).

This keeps provider-, tool-, and retrieval-specific code in its own Extra without modifying modAI core.

## Major components

### Manager UI

The Manager UI handles user-facing interactions.

Examples:

- Chat panel (global button and media browser)
- Prompt dialogs and field actions
- Image generation and vision actions
- Tool, agent, context provider, and prompt-library administration
- Provider configuration screens

The UI calls shared API endpoints instead of talking to provider APIs directly.

### Capability layer

The capability layer defines what modAI can do in provider-neutral terms.

Current capabilities include:

- Chat
- Text generation
- Image generation
- Vision analysis
- Function calling (tools)
- Vector search and retrieval (context providers)

### Provider layer

The provider layer handles communication with external AI services. Provider implementations are responsible for:

- Authentication
- Request formatting
- Response parsing
- Model selection
- Provider-specific errors and limits

Provider-specific code stays out of Manager UI code.

### Configuration layer

Configuration handles provider credentials, model settings, feature toggles, and user choices. It should be clear enough for site builders and safe enough for production. Sensitive credentials are stored and handled according to MODX security expectations, and can optionally execute server-side (`modai.api.*.execute_on_server`) to avoid exposing keys to the browser.

### Agents, tools, and prompts

Agents bundle a model, a system prompt, model parameters, tools, and context providers into a reusable assistant. Tools are server-side capabilities an agent can call (function calling); context providers supply grounded, site-specific context. The Prompt Library stores reusable prompts. These are configured in the Manager and stored in the data models listed above.

## Provider and capability relationship

A provider may support some capabilities and not others. For example, Anthropic supports chat and vision but not image generation. The Manager exposes only the features available for the configured provider and model. See [PROVIDERS.md](PROVIDERS.md) for the capability matrix.

## Shipped advanced capabilities

These are implemented today and are areas of active improvement:

### Function calling (tools)

Agents can call server-side tools through the model. Tool access is explicit and scoped: each tool declares its parameters (as JSON Schema), a prompt, and a permission check, and a tool runs only when it is marked default or attached to the selected agent.

### Vector search and RAG

Context providers ground responses in site-specific content. The built-in Pinecone provider performs retrieval with reranking and can automatically index MODX resources, chunks, snippets, and templates as they change.

## Future architecture areas

### Site-aware assistants

Future assistants may help users work with a specific MODX site — finding related content, suggesting internal links, drafting page updates, or explaining site structure. These should respect permissions and avoid changing content without user approval.

### Workflow automation

Automation should grow from explicit tools and user-approved actions — repetitive content cleanup, metadata generation, draft creation, content review, media description, and translation support. Automation should be inspectable and reversible where possible.

## Contributor guidance

When adding a feature, ask:

1. Is this a capability, a provider implementation, or a Manager workflow?
2. Can another provider support this later?
3. Does this belong in shared code or provider-specific code?
4. Does the user remain in control?
5. Does this fit the design principles?

Read [DESIGN_PRINCIPLES.md](DESIGN_PRINCIPLES.md) before making architecture changes.
