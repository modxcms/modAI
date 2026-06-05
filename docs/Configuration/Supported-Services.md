---
sidebar_position: 2
---

# Supported AI Services

### Executing AI requests

:::warning
By default your keys will be exposed to manager users over network requests, so it is recommended to use a site-specific key
that can be easily revoked.
:::

Enabling system setting `modai.api.execute_on_server` will move the execution to the server side, hiding the network traffic.

It can be enabled per service using the `modai.api.{service}.execute_on_server` format. For example, to enable this only for ChatGPT, the setting would be: `modai.api.openai.execute_on_server`.

## OpenAI (ChatGPT)

- Service name: openai
- Supported modes:
  - **text generation** – [](https://platform.openai.com/docs/guides/text-generation)
  - **image to text** – [](https://platform.openai.com/docs/guides/vision)
  - **DALL-E image creation** – [](https://platform.openai.com/docs/guides/images)

ChatGPT is the default AI service assumed. Fill out the `modai.api.openai.key` and adjust any models as desired.

## Google Gemini

- Service name: google
- Supported modes:
  - **text generation** - [](https://ai.google.dev/gemini-api/docs/models/gemini)
  - **image to text** – [](https://ai.google.dev/gemini-api/docs/vision)
  - **image generation** – [](https://ai.google.dev/gemini-api/docs/imagen)

Add a valid API key to the `modai.api.google.key` to use Google Gemini.

To change a prompt to use Google Gemini, set its corresponding model setting, e.g:

- `global.global.model` → `google/gemini-2.0-flash`

## Anthropic (Claude)

:::warning
At this moment Claude can only work server-side. Set either `modai.api.execute_on_server` or `modai.api.anthropic.execute_on_server` to `true`.
:::

- Service name: anthropic
- Supported modes:
  - **text generation** - [](https://docs.anthropic.com/en/docs/about-claude/models)
  - **image to text** - [](https://docs.anthropic.com/en/docs/about-claude/models)

Add a valid API key to the `modai.api.anthropic.key` to use Claude.

To change a prompt to use Claude, set its corresponding model setting, e.g:

- `global.global.model` → `anthropic/claude-3-5-haiku-latest`

## OpenRouter.ai

- Service name: openrouter
- Supported modes:
  - **text generation** - [](https://openrouter.ai/models?fmt=cards&output_modalities=text)
  - **image to text** - [](https://openrouter.ai/models?fmt=cards&input_modalities=image)

Add a valid API key to the `modai.api.openrouter.key` to use OpenRouter.

To change a prompt to use OpenRouter, set its corresponding model setting, e.g:

- `global.global.model` → `openrouter/meta-llama/llama-4-scout:free`

## Custom Services/Models

- Service name: custom

Some services like [Open WebUI](https://docs.openwebui.com) provide a wrapper for multiple models. To use a custom model via these services you need to fill out the `modai.api.custom.url`, `modai.api.custom.key` and optionally the `modai.api.custom.compatibility`, which tells modAI which API wire-format to use.

To use the custom service, set the following fields:

- `modai.api.custom.url` → `{your custom URL}`
- `modai.api.custom.key` → `{your API key}`

Then, for each model you want to use, set the corresponding "model" field with the prefix "custom/" followed by the model name, e.g:

- `modai.global.model` → `custom/llama3.1:8b`

The custom service speaks the OpenAI wire format and supports **text**, **vision**, **image generation**, and **image editing** (when image attachments are supplied). It calls `{url}/chat/completions`, `{url}/images/generations`, and `{url}/images/edits`.

:::note
`modai.api.custom.compatibility` defaults to `openai`, which is currently the only supported value — it's a forward-looking placeholder for future wire formats. Leave it as `openai`.
:::

## Registering Custom Services

Beyond the OpenAI-compatible `custom` service above, developers can register an entirely new AI service in code by hooking the **`modAIOnServiceRegister`** event.

A plugin on this event returns the fully-qualified class name(s) of your service classes (a JSON-encoded array matches the core convention):

```php
$modx->event->output(json_encode([
    \MyExtra\Services\MyService::class,
]));
```

Each class must implement `\modAI\Services\AIService`:

```php
public static function getServiceName(): string;          // e.g. "myservice" -> reads modai.api.myservice.key
public static function isMyModel(string $model): bool;    // claim a model prefix, e.g. "myservice/"
public function __construct(modX &$modx);
public function getCompletions(array $data, CompletionsConfig $config): AIResponse;  // text / chat
public function getVision(string $prompt, string $image, VisionConfig $config): AIResponse;
public function generateImage(string $prompt, ImageConfig $config): AIResponse;
```

modAI resolves a `service/model` string by asking each registered service's `isMyModel()` whether it owns that **model prefix** — so pick a unique prefix that doesn't collide with the built-ins (`openai/`, `google/`, `anthropic/`, `openrouter/`, `custom/`). The part after the prefix is the real model id.

Services don't make the HTTP call themselves. They build and return an `AIResponse` (`\modAI\Services\Response\AIResponse`) describing the request — URL, headers, body, parser, and streaming flag — and modAI executes it:

```php
return AIResponse::new(self::getServiceName(), $config->getRawModel())
    ->withStream($config->isStream())
    ->withParser('content')                 // 'content' for chat/vision, 'image' for image generation
    ->withUrl(self::COMPLETIONS_API)
    ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
    ->withBody($input);
```

The `ApiKey` trait (`\modAI\Services\ApiKey`) provides a `getApiKey()` helper that reads `modai.api.{getServiceName()}.key`. Look at `\modAI\Services\OpenRouter` for a clean reference implementation. A service must implement all three modes, but unsupported ones can throw a `LexiconException`.
