# Supported Providers

### Executing AI requests
<warning id="key-warning">
    By default your keys will be exposed to manager users over network requests, so it is recommended to use a site-specific key 
    that can be easily revoked.
</warning>

Enabling system setting `modai.api.execute_on_server` will move the execution to the server side, hiding the network traffic.

It can be enabled per service using `modai.api.{service}.execute_on_server}` format, for example, to enable this only for chatgpt, the setting would be: `modai.api.chatgpt.execute_on_server`.

## ChatGPT (OpenAI)

ChatGPT is the default model assumed. Fill out the `modai.api.chatgpt.key` and adjust any models as desired.

- **text generation** – https://platform.openai.com/docs/guides/text-generation
- **image to text** – https://platform.openai.com/docs/guides/vision
- **DALL-E image creation** – https://platform.openai.com/docs/guides/images

## Google Gemini

Add a valide API key to the `modai.api.gemini.key` to use Google Gemini.

For details on current Gemini models supported, consult their documentation:

- **text generation** - https://ai.google.dev/gemini-api/docs/models/gemini
- **image to text** – https://ai.google.dev/gemini-api/docs/vision
- **image generation** – https://ai.google.dev/gemini-api/docs/imagen

To change a prompt to use Google Gemini, set its corresponding model setting, e.g:

- `global.global.model` → `gemini-2.0-flash`

## Claude (Anthropic)

Claude currently only supports text generation. Add a valide API key to the `modai.api.claude.key` to use Claude.

- **text generation** - https://docs.anthropic.com/en/docs/about-claude/models

To change a prompt to use Claude, set its corresponding model setting, e.g:

- `global.global.model` → `claude-3-5-haiku-latest`


## Custom Services/Models

Some services like [Open WebUI](https://docs.openwebui.com) provide a wrapper for multiple models. To use a custom model via these services you need to fill out the `modai.api.custom.url`, `modai.api.custom.key` and optionally the `modai.custom.compatibility`, which tells the model what API emulation to use (almost alway leave this as openai).

To use the custom service, set the following fields:

- `modai.api.custom.url` → `{your custom URL}`
- `modai.api.custom.key` → `{your API key}`

Then, you for each model you want to use, set the corresponding "model" field with the prefix "custom_" followed by the model name, e.g:

- `modai.global.model` → `custom_llama3.1:8b

<seealso>
   <category ref="external">
       <a href="https://platform.openai.com/docs/guides/">OpenAI Guides</a>
       <a href="https://ai.google.dev/gemini-api/docs/">Gemini Docs</a>
       <a href="https://docs.anthropic.com/en/docs/">Anthropic Docs</a>
       <a href="https://docs.openwebui.com">Open WebUI Docs</a>
   </category>
</seealso>
