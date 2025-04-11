# Initialization

The configuration system provides a way to set up and initialize the application with necessary settings and resources.

## Config Type

Defines the application configuration structure.

### Properties
- `name` (string, optional): Application name
- `assetsURL` (string): Base URL for application assets
- `apiURL` (string): Base URL for API endpoints
- `cssURL` (string): URL for application stylesheets
- `translateFn` (function, optional): Translation function for internationalization
- `availableAgents` (Record<string, AvailableAgent>): Map of available AI agents

## Initialization

The `ModAI.init` function sets up the application with the provided configuration. It's recommended to get the config from the modAI service class `$modAI->getBaseConfig()`. 

### Parameters
- `config` (Config): Application configuration object

### Returns
An object containing initialized modules:
- `chatHistory`: Chat history management
- `history`: History tracking
- `executor`: Command execution
- `ui`: User interface components
- `lng`: Language/translation utilities
- `initOnResource`: Resource initialization

## Example

```php
if (!$modx->services->has('modai')) {
    return;
}

/** @var \modAI\modAI | null $modAI */
$modAI = $modx->services->get('modai');

if ($modAI === null) {
    return;
}

foreach ($modAI->getUILexiconTopics() as $topic) {
    $modx->controller->addLexiconTopic($topic);
}

$baseConfig = $modAI->getBaseConfig();
$modx->controller->addHtml('
    <script type="text/javascript">
    if (typeof modAI === "undefined") {
        let modAI;
        Ext.onReady(function() {
            modAI = ModAI.init(' . json_encode($baseConfig) . ');
        });
    }
    </script>
');

$this->modx->regClientStartupScript($this->modAI->getJSFile());
``` 
