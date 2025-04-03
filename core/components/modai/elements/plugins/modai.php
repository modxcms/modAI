<?php
/**
 * @var \MODX\Revolution\modX $modx
 * @var array $scriptProperties
 */

if (!$modx->services->has('modai')) {
    return;
}

/** @var \modAI\modAI | null $modAI */
$modAI = $modx->services->get('modai');

if ($modAI === null) {
    return;
}

$class = '\\modAI\\Elements\\Events\\' . $modx->event->name;
if (!class_exists($class)) {
    $modx->log(\xPDO::LOG_LEVEL_ERROR, "Class $class not found");
    return;
}

/** @var \modAI\Elements\Events\Event $event */
$event = new $class($modAI, $scriptProperties);
$event->run();