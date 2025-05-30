<?php

/**
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace
 */

require_once $namespace['path'] . 'vendor/autoload.php';

$modx->addPackage('modAI\Model', $namespace['path'] . 'src/', null, 'modAI\\');

if (!$modx->services->has('modai')) {
    $modx->services->add('modai', function ($c) use ($modx) {
        try {
            return new \modAI\modAI($modx);
        } catch (\Exception $e) {
            return null;
        }
    });
}
