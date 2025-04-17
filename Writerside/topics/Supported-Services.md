# Supported AI Services

### Executing AI requests
<warning id="key-warning">
    By default your keys will be exposed to manager users over network requests, so it is recommended to use a site-specific key 
    that can be easily revoked.
</warning>

Enabling system setting `modai.api.execute_on_server` will move the execution to the server side, hiding the network traffic.

It can be enabled per service using `modai.api.{service}.execute_on_server}` format, for example, to enable this only for chatgpt, the setting would be: `modai.api.anthropic.execute_on_server`.

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

Add a valide API key to the `modai.api.google.key` to use Google Gemini.

To change a prompt to use Google Gemini, set its corresponding model setting, e.g:

- `global.global.model` → `google/gemini-2.0-flash`

## Anthropic (Claude)

<warning id="claude-server-only">
    At this moment Claude can only work serverside. Setting either "modai.api.execute_on_server" or "modai.api.claude.execute_on_server" to "true".
</warning>

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

Some services like [Open WebUI](https://docs.openwebui.com) provide a wrapper for multiple models. To use a custom model via these services you need to fill out the `modai.api.custom.url`, `modai.api.custom.key` and optionally the `modai.custom.compatibility`, which tells the model what API emulation to use (almost alway leave this as openai).

To use the custom service, set the following fields:

- `modai.api.custom.url` → `{your custom URL}`
- `modai.api.custom.key` → `{your API key}`

Then, you for each model you want to use, set the corresponding "model" field with the prefix "custom/" followed by the model name, e.g:

- `modai.global.model` → `custom/llama3.1:8b`

<seealso>
   <category ref="external">
       <a href="https://platform.openai.com/docs/guides/">OpenAI Guides</a>
       <a href="https://ai.google.dev/gemini-api/docs/">Gemini Docs</a>
       <a href="https://docs.anthropic.com/en/docs/">Anthropic Docs</a>
       <a href="https://openrouter.ai/">OpenRouter Docs</a>
       <a href="https://docs.openwebui.com">Open WebUI Docs</a>
   </category>
</seealso>
