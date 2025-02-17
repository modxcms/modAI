<?php
/**
 * AutoSummaryPlugin
 * Updated version: Safely retrieves the action property and injects our assets.
 */

if ($modx->event->name !== 'OnManagerPageBeforeRender') {
    return;
}

if (!$modx->services->has('modai')) return;

$modAI = $modx->services->get('modai');

// Initialize action to an empty string.
$action = '';

// Check if $modx->controller is set, is an object, and has an "action" property.
if (isset($modx->controller) && is_object($modx->controller) && property_exists($modx->controller, 'action')) {
    $action = $modx->controller->action;
} elseif (isset($_REQUEST['a'])) {
    // Fallback: Use the request parameter.
    $action = $_REQUEST['a'];
}

// Adjust the check as per your Manager’s action identifiers.
if (in_array($action, ['resource/create', 'resource/update', '78', '85'])) {
    // Define the assets URL (ensure this path matches your installation).
    $assetsUrl = $modAI->getOption('assetsUrl');

    $modx->controller->addHtml('
            <script type="text/javascript">
                Ext.onReady(function() {
                    modAI.config = ' . $modx->toJSON($modAI->config) . ';
                    modAI.tvs =  ' . $modx->toJSON($modAI->getListOfTVsWithIDs()) . ';
                    modAI.resourceFields =  ' . $modx->toJSON($modAI->getResourceFields()) . ';
                });
            </script>
        ');

    $modx->regClientCSS($assetsUrl . 'css/mgr.css');
    $modx->regClientStartupScript($assetsUrl . 'js/mgr/modai.js');
    $modx->regClientStartupScript($assetsUrl . 'js/mgr/autosummary.js');
    $modx->regClientStartupScript($assetsUrl . 'js/mgr/widgets/image_prompt.window.js');
    $modx->regClientStartupScript($assetsUrl . 'js/mgr/widgets/text_prompt.window.js');
}