<?php
/**
 * @var \MODX\Revolution\modX $modx
 */

if ($modx->event->name !== 'OnManagerPageBeforeRender') {
    return;
}

if (!$modx->services->has('modai')) {
    return;
}

/** @var \modAI\modAI | null $modAI */
$modAI = $modx->services->get('modai');

if ($modAI === null) {
    return;
}

$action = '';

if (isset($modx->controller) && is_object($modx->controller) && property_exists($modx->controller, 'action')) {
    $action = $modx->controller->action;
} elseif (isset($_REQUEST['a'])) {
    $action = $_REQUEST['a'];
}

if (in_array($action, ['resource/create', 'resource/update'])) {
    foreach ($modAI->getUILexiconTopics() as $topic) {
        $modx->controller->addLexiconTopic($topic);
    }

    $baseConfig = $modAI->getBaseConfig();
    $modx->controller->addHtml('
            <script type="text/javascript">
            let modAI;
            Ext.onReady(function() {
                modAI = ModAI.init(' . json_encode($baseConfig) . ');
                
                 Ext.defer(function () {
                   modAI.initOnResource({
                      tvs:  ' . $modx->toJSON($modAI->getListOfTVsWithIDs()) . ',
                      resourceFields:  ' . $modx->toJSON($modAI->getResourceFields()) . ',
                    });
                 }, 500);
            });
            </script>
        ');

    $modx->regClientStartupScript($modAI->getJSFile());
}
