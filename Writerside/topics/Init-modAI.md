# Initialization

modAI is available globally in manager, if you need to init it elsewhere, you can follow the instructions below.

## Loading up pre-requisites
The following script will get the `modai` service (which can be `null` if it doesn't exist or if user doesn't have appropriate permissions). Then it loads lexicons needed for the modAI's UI and load the main JS file.
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

$this->modx->regClientStartupScript($this->modAI->getJSFile());
```

## Initializing modAI
When the main JS file is loaded, `ModAI.init` function will be available. It takes single `config` argument, with following parameters. We recommend using helper function to supply all necessary properties to the config `$modAI->getBaseConfig()`, you can check the usage in the example at the end of this page.

### Properties
- `name` (string, optional): Application name
- `assetsURL` (string): Base URL for application assets
- `apiURL` (string): Base URL for API endpoints
- `cssURL` (string): URL for application stylesheets
- `translateFn` (function, optional): Translation function for internationalization
- `availableAgents` (Record<string, AvailableAgent>): Map of available AI agents
- `permissions` (Record<Permissions, 1 | 0>): Map of user permissions

### Returns
An object containing initialized modules:
- `chatHistory`: Chat history management
- `history`: History tracking
- `executor`: Command execution
- `ui`: User interface components
- `lng`: Language/translation utilities
- `checkPermissions`: Function to check if user has specific permission
- `initOnResource`: Resource initialization
- `initGlobalButton`: Global button initialization

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
        Ext.onReady(function() {
            const modAI = ModAI.init(' . json_encode($baseConfig) . ');
            window.modAI = modAI;
        });
    }
    </script>
');

$this->modx->regClientStartupScript($this->modAI->getJSFile());
``` 
