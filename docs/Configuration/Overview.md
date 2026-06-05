---
sidebar_position: 1
---

# Overview

Use the [Supported Services](Supported-Services.md) section to set up your response AI service's credentials.

## Models

When defining the model name you want to use, you have to enter it in the following format: `service_name/model_name`, so for example `gpt-4o-mini` would become `openai/gpt-4o-mini`, `gemini-2.0-flash` would be `google/gemini-2.0-flash` etc.

## Generative Fields

The default fields modAI enables in the `modai.res.fields` setting are longtitle, description, introtext/summary, and content. You can add additional standard resource fields to this setting.

### Adding Generative Template Variables

If you have text, textarea, image or image+ Template Variables you wish to use with modAI, add their names to the `modai.tvs` setting.

- **text inputs** – modAI uses the base prompt and model and directly inputs the content into the field
- **textarea inputs** – handled the same as text inputs
- **richtext inputs** – handled the same as a textarea input, but you must manually copy/paste the output into the textarea
- **image inputs** – modAI uses the default image model and sizes and creates the images in the `assets/ai` directory
- **image+ TVs** – handled the same as image inputs, with the addition of being able to use the Vision model for creating alt tag captions from generated images
- **ImageCropper TVs** – supported the same way as image+ TVs

### Configuring Field Prompts

Customize the base prompts and configurations in the `global`, `image`, `resources` and `vision` areas in the system settings as needed. The `modai.global.text.base_prompt` prepends the base to every other text or textarea prompt, but it does not for image or vision model prompts.

## Settings Format

Settings use the following naming convention: `{namespace}.{field}.{type}.{setting}`.

- Where `namespace` by default is `modai`, but other extras can pass their own to override default settings from their extra.
- Field in the core modAI can be `res.pagetitle`, `res.longtitle`, `res.introtext`, `res.description`, `res.content` and `tv.tv_name` (for any text/image/image+ TV), but Extras can pass whatever makes sense for them (for example TinyMCE RTE will pass `ta` for the content RTE instance). The field can also be `global` which is a fallback for any setting.
- Type can be one of `text`/`image`/`vision` to denote this setting will get only applied to AI interaction from the specific category
- Setting is a target setting name like `model`, `temperature`, `quality`

All settings drill down from the most specific to the global one, few examples (resolves top to bottom):

```
modai.res.content.text.model
modai.global.text.model
```

```
modai.tv.poster.image.model
modai.global.image.model
```

```
tinymcerte.modai.ta.text.model
tinymcerte.modai.global.text.model
modai.ta.text.model
modai.global.text.model
```

The `setting` for text or textarea inputs can contain several options below which will override the default settings from the `global` area:

- **model** – for text or textarea, modAI defaults to `openai/gpt-5.5`
- **temperature** – defaults to `0.7`, increase this to 1 for more “creative” results or 0 for highly predictable ones
- **max_tokens** – defaults to `2048`
- **base_output** – global output rules prepended to every text/textarea prompt; the defaults instruct the model to return clean, ready-to-use content with no explanation or surrounding quotes
- **context_prompt** – template used to inject Context Provider results into the next message; defaults to `Here's context for next message, act only on this: {context}`

:::note
Default model names change between modAI releases as providers ship new models. The values below reflect the current defaults — always check your system settings for what's actually configured on your install.
:::

Image and Image+ fields do not prepend their prompts with the base prompt. Image/Image+ setting defaults in the `image` area include:

- **model** – defaults to `openai/gpt-image-2`
- **quality** – defaults to `auto`
- **size** – defaults to `1024x1024`
- **style** – no default (empty)
- **path** – where generated images are saved; defaults to `assets/ai/{resourceId}/{hash}.png`
- **media_source** – the MODX media source generated images are written to (defaults to the default source)
- **download_domains** – optional allowlist of domains modAI may download generated images from

Image+ fields can use Vision models to describe an image for the alt content found in the `vision` area:

- **vision.model** – defaults to `openai/gpt-5-nano`
- **vision.max_tokens** – defaults to `1024`

### Custom Options

If needed, custom options can be supplied to the AI service by either using the `global` variant of the setting for specific type, for example `modai.global.text.custom_options` and setting it to a JSON object with desired properties, or by using a setting for a specific field like `modai.res.content.text.custom_options`.

### Overriding Global Defaults

Each field that you attach modAI to can have settings overrides. This means that you can mix and match which models are used for specific fields, allowing you to optimize for cost or quality depending on your application. For example to use Claude Haiku to generate a (very) creative summary for the `[[*introtext]]` field, you would update/create the following settings:

- `modai.api.anthropic.key` → `{your API key}`
- `modai.res.introtext.text.model` → `anthropic/claude-3-5-haiku-20241022`
- `modai.res.introtext.text.temperature` → `0.9`

## Streaming

If you'd like to stream the AI response into the UI (see the incrementally generated response), you can do so by enabling `modai.global.text.stream` and/or `modai.global.vision.stream` (as always, you can enable this also for only specific fields).

:::warning
If you are executing requests on the server (`modai.api.execute_on_server` is set to `true`), make sure you configured your server to support streaming (disabled buffering etc.), you can make these changes only for the `/assets/components/modai/api.php` path.
:::

## Initialization & Integrations

modAI loads itself into the Manager automatically. A few settings control where it appears and how its assets are cached:

- **`modai.init.global_chat`** (default `Yes`) – shows the global AI [chat](Chat.md) button in the Manager menu.
- **`modai.init.media_browser`** (default `Yes`) – adds an AI image-generation button to the Media browser. See [Chat → Media browser integration](Chat.md#media-browser-integration).
- **`modai.cache.lit`** (default `0`) – a cache-busting version appended to modAI's JS/CSS URLs. Increment it to force browsers to reload modAI's assets after an update.

To automatically index MODX content into a Pinecone vector database for retrieval, see the `modai.contexts.*` settings in [Context Providers → How indexing works](../Admin/Context-Providers.md#how-indexing-works).
