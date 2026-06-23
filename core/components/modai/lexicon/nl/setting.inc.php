<?php

$_lang['setting_modai.api.openai.key'] = 'OpenAI API Key';
$_lang['setting_modai.api.openai.key_desc'] = 'Je API key van https://platform.openai.com/api-keys';
$_lang['setting_modai.api.google.key'] = 'Google API Key';
$_lang['setting_modai.api.google.key_desc'] = 'Je API key van https://ai.google.dev/gemini-api/docs/api-key';
$_lang['setting_modai.api.anthropic.key'] = 'Anthropic API Key';
$_lang['setting_modai.api.anthropic.key_desc'] = 'Je API key van https://console.anthropic.com/settings/keys';
$_lang['setting_modai.api.openrouter.key'] = 'OpenRouter.ai API Key';
$_lang['setting_modai.api.openrouter.key_desc'] = 'Je API key van https://openrouter.ai/settings/keys';
$_lang['setting_modai.api.custom.key'] = 'Custom API Key';
$_lang['setting_modai.api.custom.key_desc'] = '';
$_lang['setting_modai.api.custom.url'] = 'Custom API URL';
$_lang['setting_modai.api.custom.url_desc'] = '';
$_lang['setting_modai.api.custom.compatibility'] = 'Custom API Compatibiliteit';
$_lang['setting_modai.api.custom.compatibility_desc'] = 'API compatibiliteitstype. Beschikbare opties: openai';

$_lang['setting_modai.global.text.base_output'] = 'Basis Output Instructies';
$_lang['setting_modai.global.text.base.output_desc'] = 'Dit vertelt het model hoe de resultaten uit te voeren om commentaar, aanhalingstekens, etc. te verwijderen.';
$_lang['setting_modai.global.text.base_prompt'] = 'Basis Prompt';
$_lang['setting_modai.global.text.base_prompt_desc'] = 'Dit is een algemene instructie die standaard aan elke API-aanvraag wordt toegevoegd, vergelijkbaar met wat je zou invoeren voor Customize ChatGPT op hun website.';
$_lang['setting_modai.global.text.max_tokens'] = 'Standaard Maximum Tokens';
$_lang['setting_modai.global.text.max_tokens_desc'] = 'Om kosten te beheren, is 1000 tokens ongeveer gelijk aan 750 woorden. Stel een waarde van -1 in om uit te schakelen.';
$_lang['setting_modai.global.text.model'] = 'Standaard AI Model';
$_lang['setting_modai.global.text.model_desc'] = 'Het standaard model voor alle prompts, tenzij overschreven per prompt.';
$_lang['setting_modai.global.text.temperature'] = 'Standaard AI Temperatuur';
$_lang['setting_modai.global.text.temperature_desc'] = 'Hogere waarden zoals 0.8 zijn meer willekeurig en creatief, terwijl waarden lager dan 0.2 meer gefocust en deterministisch zijn. Stel een waarde van -1 in om uit te schakelen.';
$_lang['setting_modai.global.text.context_prompt'] = 'Context Prompt';
$_lang['setting_modai.global.text.context_prompt_desc'] = 'Prompt die gebruikt wordt bij het doorgeven van aanvullende context aan de chat.';
$_lang['setting_modai.global.text.stream'] = 'Stream';
$_lang['setting_modai.global.text.stream_desc'] = 'Indien ingeschakeld en uitvoeren op server is uitgeschakeld, zullen ondersteunde modellen de respons streamen.';
$_lang['setting_modai.global.text.custom_options'] = 'Aangepaste Opties';
$_lang['setting_modai.global.text.custom_options_desc'] = 'Een JSON object van aangepaste opties die doorgegeven worden aan de body van de AI-aanvraag. Raadpleeg de documentatie van je model voor ondersteunde opties.';

$_lang['setting_modai.global.vision.model'] = 'Vision Model';
$_lang['setting_modai.global.vision.model_desc'] = 'Maak tekst van afbeelding inputs. Geldige opties zijn o1, gpt-4o, gpt-4o-mini, en gpt-4-turbo.';
$_lang['setting_modai.global.vision.prompt'] = 'Vision Prompt';
$_lang['setting_modai.global.vision.prompt_desc'] = 'Standaard zou dit een 120 karakters alt tag moeten maken gebaseerd op de vision API output.';
$_lang['setting_modai.global.vision.stream'] = 'Stream';
$_lang['setting_modai.global.vision.stream_desc'] = 'Indien ingeschakeld en uitvoeren op server is uitgeschakeld, zullen ondersteunde modellen de respons streamen.';
$_lang['setting_modai.global.vision.custom_options'] = 'Aangepaste Opties';
$_lang['setting_modai.global.vision.custom_options_desc'] = 'Een JSON object van aangepaste opties die doorgegeven worden aan de body van de AI-aanvraag. Raadpleeg de documentatie van je model voor ondersteunde opties.';
$_lang['setting_modai.global.vision.max_tokens'] = 'Standaard Maximum Tokens';
$_lang['setting_modai.global.vision.max_tokens_desc'] = 'Om kosten te beheren, is 1000 tokens ongeveer gelijk aan 750 woorden.';

$_lang['setting_modai.global.image.model'] = 'Afbeelding Model';
$_lang['setting_modai.global.image.model_desc'] = 'Geldige opties zijn `dall-e-2` en `dall-e-3` (standaard). Zie https://platform.openai.com/docs/guides/images/image-generation-beta voor volledige details inclusief DALL-E-2 specifieke informatie.';
$_lang['setting_modai.global.image.size'] = 'Afbeelding Afmetingen';
$_lang['setting_modai.global.image.size_desc'] = 'Geldige opties voor DALL-E-3 zijn `1024x1024`, `1792x1024` (standaard), en `1024x1792`.';
$_lang['setting_modai.global.image.quality'] = 'Afbeelding Kwaliteit';
$_lang['setting_modai.global.image.quality_desc'] = 'Geldige opties zijn `standard` (standaard) en `hd`.';
$_lang['setting_modai.global.image.path'] = 'Afbeelding Pad';
$_lang['setting_modai.global.image.path_desc'] = 'Pad inclusief bestandsnaam waar de AI gegenereerde afbeelding wordt opgeslagen. Beschikbare placeholders: {hash}, {shortHash}, {resourceId}, {year}, {month}, {day}.';
$_lang['setting_modai.global.image.download_domains'] = 'Toegestane Download Domeinen';
$_lang['setting_modai.global.image.download_domains_desc'] = 'Aanvullende domeinen om het downloaden van gegenereerde afbeeldingen van toe te staan.';
$_lang['setting_modai.global.image.media_source'] = 'Media Source';
$_lang['setting_modai.global.image.media_source_desc'] = 'Standaard media source voor afbeelding uploads (tenzij veld zijn eigen specificeert).';
$_lang['setting_modai.global.image.style'] = 'Stijl';
$_lang['setting_modai.global.image.style_desc'] = 'Geldige opties voor DALL-E-3 zijn vivid en natural (standaard)';
$_lang['setting_modai.global.image.custom_options'] = 'Aangepaste Opties';
$_lang['setting_modai.global.image.custom_options_desc'] = 'Een JSON object van aangepaste opties die doorgegeven worden aan de body van de AI-aanvraag. Raadpleeg de documentatie van je model voor ondersteunde opties.';
$_lang['setting_modai.global.image.response_format'] = 'Respons Formaat';
$_lang['setting_modai.global.image.response_format_desc'] = 'Respons formaat van de afbeelding, als het model dit ondersteunt.';

$_lang['setting_modai.res.fields'] = 'Gebruiken met MODX Resource Velden';
$_lang['setting_modai.res.fields_desc'] = 'De standaard Resource velden om modAI generatieve AI knoppen aan te koppelen.';
$_lang['setting_modai.res.pagetitle.text.prompt'] = '[[*pagetitle]] Prompt';
$_lang['setting_modai.res.pagetitle.text.prompt_desc'] = '';
$_lang['setting_modai.res.longtitle.text.prompt'] = '[[*longtitle]] Prompt';
$_lang['setting_modai.res.longtitle.text.prompt_desc'] = 'Gebruik dit voor de meta titel — werkt ook met SEO Suite.';
$_lang['setting_modai.res.introtext.text.prompt'] = '[[*introtext]] Prompt';
$_lang['setting_modai.res.introtext.text.prompt_desc'] = 'Gebruik dit om een samenvatting van de pagina content te genereren — gebruikt als startpunt voor afbeeldinggeneratie.';
$_lang['setting_modai.res.description.text.prompt'] = '[[*description]] Prompt';
$_lang['setting_modai.res.description.text.prompt_desc'] = 'Gebruik dit voor de meta beschrijving — werkt ook met SEO Suite.';

$_lang['setting_modai.tvs'] = 'Gebruiken met Template Variables';
$_lang['setting_modai.tvs_desc'] = 'De Template Variable velden om modAI generatieve AI knoppen aan te koppelen. Moet een text, textarea, image of Image+ Input Type zijn.';
$_lang['setting_modai.api.execute_on_server'] = 'AI\'s aanvragen uitvoeren op server';
$_lang['setting_modai.api.execute_on_server_desc'] = 'Indien ingeschakeld, worden alle AI aanvragen uitgevoerd aan de serverzijde. Wees bewust van verhoogde belasting voor je server.';

$_lang['setting_modai.cache.lit'] = 'Laatste Installatietijd';
$_lang['setting_modai.cache.lit_desc'] = '';

$_lang['setting_modai.contexts.resources.name'] = 'Resources Context Provider Naam';
$_lang['setting_modai.contexts.resources.name_desc'] = 'Naam van de context provider die gebruikt wordt om resources te indexeren. Deze moet ingeschakeld zijn.';
$_lang['setting_modai.contexts.chunks.name'] = 'Chunks Context Provider Naam';
$_lang['setting_modai.contexts.chunks.name_desc'] = 'Naam van de context provider die gebruikt wordt om chunks te indexeren. Deze moet ingeschakeld zijn.';
$_lang['setting_modai.contexts.snippets.name'] = 'Snippets Context Provider Naam';
$_lang['setting_modai.contexts.snippets.name_desc'] = 'Naam van de context provider die gebruikt wordt om snippets te indexeren. Deze moet ingeschakeld zijn.';
$_lang['setting_modai.contexts.templates.name'] = 'Templates Context Provider Naam';
$_lang['setting_modai.contexts.templates.name_desc'] = 'Naam van de context provider die gebruikt wordt om templates te indexeren. Deze moet ingeschakeld zijn.';

$_lang['setting_modai.chat.additional_controls'] = 'Aanvullende Chat Controls';
$_lang['setting_modai.chat.additional_controls_desc'] = 'Definieer aanvullende opties voor het chatvenster. Bekijk docs voor meer info: https://modxcms.github.io/modAI';
$_lang['setting_modai.chat.title.generate'] = 'Automatisch Chat Titel Genereren';
$_lang['setting_modai.chat.title.generate_desc'] = 'Indien ingeschakeld, wordt chat.title.model gebruikt om een titel voor een chat te genereren.';
$_lang['setting_modai.chat.title.model'] = 'Chat Titel Model';
$_lang['setting_modai.chat.title.model_desc'] = 'Model gebruikt om een titel voor een chat te genereren.';
$_lang['setting_modai.chat.title.prompt'] = 'Chat Titel Prompt';
$_lang['setting_modai.chat.title.prompt_desc'] = 'Systeeminstructies over hoe een titel voor een chat te genereren.';
$_lang['setting_modai.chat.title.model_options'] = 'Chat Titel Model Opties';
$_lang['setting_modai.chat.title.model_options_desc'] = 'Opties die doorgegeven worden aan het LLM model bij het genereren van een chat titel.';


$_lang['setting_modai.init.global_chat'] = 'Globale Chat';
$_lang['setting_modai.init.global_chat_desc'] = 'Indien ingeschakeld, toont modAI een globale chat knop in de hoofdnavigatie.';
$_lang['setting_modai.init.media_browser'] = 'Media Browser';
$_lang['setting_modai.init.media_browser_desc'] = 'Indien ingeschakeld, toont modAI een genereer icoon in de media browser.';
