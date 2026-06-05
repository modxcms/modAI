# Providers

modAI supports multiple AI providers through a shared provider layer.

The provider layer allows the Manager to use AI capabilities without hard-coding provider-specific API behavior into user-facing workflows.

## Supported providers

Current provider support includes:

- OpenAI
- Anthropic Claude
- Google Gemini
- OpenRouter
- Custom / OpenAI-compatible endpoints

Provider support may vary by capability and model.

## Capability matrix

| Capability | OpenAI | Anthropic Claude | Google Gemini | OpenRouter | Custom (OpenAI-compatible) |
| --- | --- | --- | --- | --- | --- |
| Chat / text generation | Yes | Yes | Yes | Yes | Yes |
| Streaming responses | Yes | Yes | Yes | Yes | Yes |
| Vision analysis | Yes | Yes | Yes | Yes | Yes |
| Image generation | Yes | No | Yes | Yes | Yes |
| Function calling (tools) | Yes | Yes | Yes | Yes¹ | Yes¹ |

¹ Model-dependent. OpenRouter and custom endpoints route to many underlying models; vision, image, and tool support depend on the selected model.

This matrix should be updated as provider support changes.

> **Embeddings, vector search, and RAG** are provided through [Context Providers](https://modxcms.github.io/modAI/Admin/Context-Providers), not through a provider-level embeddings capability. The built-in Pinecone provider relies on the vector database's own integrated embeddings rather than computing embeddings through an AI provider.

## How providers work

A provider is a PHP class implementing `\modAI\Services\AIService`. The key pieces:

- **`getServiceName()`** returns a short identifier (e.g. `openai`) used to look up the `modai.api.{service}.key` setting.
- **`isMyModel(string $model)`** claims a model prefix (e.g. `openai/`, `anthropic/`, `custom/`). `AIServiceFactory` resolves a `service/model` string by asking each registered service whether it owns that prefix.
- **`getCompletions()` / `getVision()` / `generateImage()`** build and return an `\modAI\Services\Response\AIResponse` describing the request (URL, headers, body, parser, streaming). modAI executes the request — the service does not call the HTTP API itself.

The five built-in services live in `core/components/modai/src/Services/`.

## Provider responsibilities

Each provider implementation should handle:

- Authentication (the `ApiKey` trait reads `modai.api.{service}.key`)
- Request formatting
- Response parsing (via the appropriate `AIResponse` parser)
- Model selection (the prefix claimed by `isMyModel()`)
- Capability support (text, vision, image)
- Provider-specific limits
- Provider-specific error handling

Provider-specific behavior should be documented.

## Adding a provider

A new provider should:

1. Implement `\modAI\Services\AIService` (use an existing service such as `OpenRouter` as a reference).
2. Claim a unique model prefix in `isMyModel()` that does not collide with the built-ins (`openai/`, `google/`, `anthropic/`, `openrouter/`, `custom/`).
3. Register the class by returning it from a plugin on the `modAIOnServiceRegister` event.
4. Add configuration fields for credentials and model settings.
5. Document supported models and known limits.
6. Include basic tests or manual test steps.
7. Avoid changes to Manager UI unless the provider needs a new shared capability.

See the [Supported Services](https://modxcms.github.io/modAI/Configuration/Supported-Services) docs for the contributor-facing details and a minimal service skeleton.

## Adding a capability to an existing provider

When adding a capability to an existing provider:

1. Confirm that the provider supports the capability in the target models.
2. Add support inside the provider implementation.
3. Update the capability matrix.
4. Add configuration notes if the feature requires a specific model or setting.
5. Add tests or documented manual test steps.

## Provider-specific behavior

Provider APIs differ.

Common differences include:

- Model names
- Token limits
- File and image formats
- Streaming formats
- Rate limits
- Safety filtering
- Error payloads
- Tool calling syntax
- Pricing models

Keep those differences inside the provider layer when possible.

## Credentials

Provider credentials should be handled with care.

Do not commit API keys or test credentials.

Documentation and examples should use placeholders.

Example:

```text
YOUR_PROVIDER_API_KEY
```

## Local testing

When testing provider changes:

1. Test with valid credentials.
2. Test missing credentials.
3. Test invalid credentials.
4. Test provider rate limit or error responses where practical.
5. Test unsupported model or capability combinations.
6. Confirm the Manager shows a useful error.

## Documentation updates

Provider changes should update this file when they change:

- Supported providers
- Supported capabilities
- Configuration requirements
- Model requirements
- Known limitations
