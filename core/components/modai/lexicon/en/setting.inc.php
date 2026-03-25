<?php

$_lang['setting_modai.api.openai.key'] = 'OpenAI API Key';
$_lang['setting_modai.api.openai.key_desc'] = 'Your API key found at https://platform.openai.com/api-keys';
$_lang['setting_modai.api.google.key'] = 'Google API Key';
$_lang['setting_modai.api.google.key_desc'] = 'Your API key found at https://ai.google.dev/gemini-api/docs/api-key';
$_lang['setting_modai.api.anthropic.key'] = 'Anthropic API Key';
$_lang['setting_modai.api.anthropic.key_desc'] = 'Your API key found at https://console.anthropic.com/settings/keys';
$_lang['setting_modai.api.openrouter.key'] = 'OpenRouter.ai API Key';
$_lang['setting_modai.api.openrouter.key_desc'] = 'Your API key found at https://openrouter.ai/settings/keys';
$_lang['setting_modai.api.custom.key'] = 'Custom API Key';
$_lang['setting_modai.api.custom.key_desc'] = '';
$_lang['setting_modai.api.custom.url'] = 'Custom API URL';
$_lang['setting_modai.api.custom.url_desc'] = '';
$_lang['setting_modai.api.custom.compatibility'] = 'Custom API Compatibility';
$_lang['setting_modai.api.custom.compatibility_desc'] = 'API compatibility type. Available options: openai';

$_lang['setting_modai.global.text.base_output'] = 'Base Output Instructions';
$_lang['setting_modai.global.text.base.output_desc'] = 'Instructions for formatting AI responses (e.g., remove extra commentary, strip quotes, etc.).';
$_lang['setting_modai.global.text.base_prompt'] = 'Base Prompt';
$_lang['setting_modai.global.text.base_prompt_desc'] = 'General instructions added to every AI request. Similar to how you might create a custom ChatGPT on OpenAI’s website.';
$_lang['setting_modai.global.text.max_tokens'] = 'Default Maximum Tokens';
$_lang['setting_modai.global.text.max_tokens_desc'] = 'Limit response length to control costs. Roughly: 1,000 tokens ≈ 750 words.';
$_lang['setting_modai.global.text.model'] = 'Default AI Model';
$_lang['setting_modai.global.text.model_desc'] = 'The default model for all prompts unless overridden per prompt.';
$_lang['setting_modai.global.text.temperature'] = 'Default AI Temperature';
$_lang['setting_modai.global.text.temperature_desc'] = 'Controls AI creativity: Higher values (like 0.8) = more creative but unpredictable; Lower values (like 0.2) = more focused and consistent. Set to -1 to disable.';
$_lang['setting_modai.global.text.context_prompt'] = 'Context Prompt';
$_lang['setting_modai.global.text.context_prompt_desc'] = 'Instructions for how the AI should use extra information added to a chat prompt.';
$_lang['setting_modai.global.text.stream'] = 'Stream';
$_lang['setting_modai.global.text.stream_desc'] = 'If enabled, responses will appear word-by-word (if your browser supports it), instead of waiting for the entire response.';
$_lang['setting_modai.global.text.custom_options'] = 'Custom Options';
$_lang['setting_modai.global.text.custom_options_desc'] = 'For advanced users: pass additional settings to the AI service as JSON. Check your AI provider’s documentation for available options.';

$_lang['setting_modai.global.vision.model'] = 'Vision Model';
$_lang['setting_modai.global.vision.model_desc'] = 'AI model to use for analyzing images and creating descriptions. Options: o1, gpt-4o, gpt-4o-mini, gpt-4-turbo.';
$_lang['setting_modai.global.vision.prompt'] = 'Vision Prompt';
$_lang['setting_modai.global.vision.prompt_desc'] = 'Instructions for creating image descriptions (alt tags). By default, creates a 120-character description for accessibility.';
$_lang['setting_modai.global.vision.stream'] = 'Stream';
$_lang['setting_modai.global.vision.stream_desc'] = 'If enabled, responses will appear word-by-word (if your browser supports it), instead of waiting for the entire response.';
$_lang['setting_modai.global.vision.custom_options'] = 'Custom Options';
$_lang['setting_modai.global.vision.custom_options_desc'] = 'A JSON object of custom options passed to the body of the AI request. Please consult the documentation for your model for supported options.';
$_lang['setting_modai.global.vision.max_tokens'] = 'Default Maximum Tokens';
$_lang['setting_modai.global.vision.max_tokens_desc'] = 'Limit image description length to control costs. Roughly: 1,000 tokens ≈ 750 words.';

$_lang['setting_modai.global.image.model'] = 'Image Model';
$_lang['setting_modai.global.image.model_desc'] = 'AI model to use for generating images. Options: `dall-e-2` or `dall-e-3` (default). See OpenAI docs for details.';
$_lang['setting_modai.global.image.size'] = 'Image Dimensions';
$_lang['setting_modai.global.image.size_desc'] = 'Valid options for DALL-E-3 are `1024x1024`, `1792x1024` (default), and `1024x1792`.';
$_lang['setting_modai.global.image.quality'] = 'Image Quality';
$_lang['setting_modai.global.image.quality_desc'] = 'Valid options are `standard` (default) and `hd`.';
$_lang['setting_modai.global.image.path'] = 'Image Path';
$_lang['setting_modai.global.image.path_desc'] = 'Path including file name where the AI generated image will be stored. Available placeholders: {hash}, {shortHash}, {resourceId}, {year}, {month}, {day}.';
$_lang['setting_modai.global.image.download_domains'] = 'Allowed Download Domains';
$_lang['setting_modai.global.image.download_domains_desc'] = 'Enter additional website domains you trust for downloading generated images from (space or comma-separated).';
$_lang['setting_modai.global.image.media_source'] = 'Media Source';
$_lang['setting_modai.global.image.media_source_desc'] = 'Default location where AI-generated images will be saved (can be overridden per field).';
$_lang['setting_modai.global.image.style'] = 'Style';
$_lang['setting_modai.global.image.style_desc'] = 'Choose the artistic style for DALL-E-3 generated images: "vivid" for highly stylized, or "natural" for realistic (default).';
$_lang['setting_modai.global.image.custom_options'] = 'Custom Options';
$_lang['setting_modai.global.image.custom_options_desc'] = 'A JSON object of custom options passed to the body of the AI request. Please consult the documentation for your model for supported options.';
$_lang['setting_modai.global.image.response_format'] = 'Response Format';
$_lang['setting_modai.global.image.response_format_desc'] = 'Format for generated images (e.g., URL or data format). Depends on your AI provider’s capabilities.';

$_lang['setting_modai.res.fields'] = 'Use with MODX Resource Fields';
$_lang['setting_modai.res.fields_desc'] = 'Choose which content fields should have AI generation buttons (like buttons to auto-generate page titles or descriptions).';
$_lang['setting_modai.res.pagetitle.text.prompt'] = '[[*pagetitle]] Prompt';
$_lang['setting_modai.res.pagetitle.text.prompt_desc'] = '';
$_lang['setting_modai.res.longtitle.text.prompt'] = '[[*longtitle]] Prompt';
$_lang['setting_modai.res.longtitle.text.prompt_desc'] = 'Use this for the meta title—also works with SEO Suite.';
$_lang['setting_modai.res.introtext.text.prompt'] = '[[*introtext]] Prompt';
$_lang['setting_modai.res.introtext.text.prompt_desc'] = 'Use this to generate a summary of the page content—used as starting point for image generation.';
$_lang['setting_modai.res.description.text.prompt'] = '[[*description]] Prompt';
$_lang['setting_modai.res.description.text.prompt_desc'] = 'Use this for the meta description—also works with SEO Suite.';

$_lang['setting_modai.tvs'] = 'Use with Template Variables';
$_lang['setting_modai.tvs_desc'] = 'Choose which custom fields should have AI generation buttons. Works with text, textarea, image, and Image+ field types.';
$_lang['setting_modai.api.execute_on_server'] = 'Execute AI\'s request on server';
$_lang['setting_modai.api.execute_on_server_desc'] = 'If enabled, all AI requests will be processed by your server instead of the user’s browser. This may increase server load but can be more reliable.';

$_lang['setting_modai.cache.lit'] = 'Last Install Time';
$_lang['setting_modai.cache.lit_desc'] = '';

$_lang['setting_modai.contexts.resources.name'] = 'Resources Context Provider Name';
$_lang['setting_modai.contexts.resources.name_desc'] = 'Name of the content source that will catalog your resources for the AI to reference. Must be enabled to work.';
$_lang['setting_modai.contexts.chunks.name'] = 'Chunks Context Provider Name';
$_lang['setting_modai.contexts.chunks.name_desc'] = 'Name of the content source that will catalog your Chunks for the AI to reference. Must be enabled to work.';
$_lang['setting_modai.contexts.snippets.name'] = 'Snippets Context Provider Name';
$_lang['setting_modai.contexts.snippets.name_desc'] = 'Name of the content source that will catalog your Snippets for the AI to reference. Must be enabled to work.';
$_lang['setting_modai.contexts.templates.name'] = 'Templates Context Provider Name';
$_lang['setting_modai.contexts.templates.name_desc'] = 'Name of the content source that will catalog your Templates for the AI to reference. Must be enabled to work.';

$_lang['setting_modai.chat.additional_controls'] = 'Additional Chat Controls';
$_lang['setting_modai.chat.additional_controls_desc'] = 'Define extra features or options for the chat window. See the documentation for details: https://modxcms.github.io/modAI';
$_lang['setting_modai.chat.title.generate'] = 'Automatically Generate Chat Title';
$_lang['setting_modai.chat.title.generate_desc'] = 'If enabled, the AI will automatically create a title summarizing each new chat conversation.';
$_lang['setting_modai.chat.title.model'] = 'Chat Title Model';
$_lang['setting_modai.chat.title.model_desc'] = 'AI model to use when automatically generating chat titles.';
$_lang['setting_modai.chat.title.prompt'] = 'Chat Title Prompt';
$_lang['setting_modai.chat.title.prompt_desc'] = 'System instructions on how to generate a title for a chat.';
$_lang['setting_modai.chat.title.model_options'] = 'Chat Title Model Options';
$_lang['setting_modai.chat.title.model_options_desc'] = 'Advanced settings sent to the AI model when it creates chat titles. Consult your provider’s documentation for available options.';


$_lang['setting_modai.init.global_chat'] = 'Global Chat';
$_lang['setting_modai.init.global_chat_desc'] = 'If enabled, users will see an AI chat button in the main admin menu.';
$_lang['setting_modai.init.media_browser'] = 'Media Browser';
$_lang['setting_modai.init.media_browser_desc'] = 'If enabled, users will see an AI generate button when browsing media files.';
