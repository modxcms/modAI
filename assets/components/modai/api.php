<?php
require_once dirname(__DIR__, 3) . '/config.core.php';
require_once MODX_CORE_PATH . 'vendor/autoload.php';

$modx = new \MODX\Revolution\modX();
$modx->initialize('mgr');

$request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
$action = $modx->getOption('action', $_REQUEST, '');

if (empty($action)) {
    \modAI\API\API::returnErrorFromAPIException(\modAI\Exceptions\APIException::badRequest());
    exit;
}

$action = str_replace('/', '\\', $action);

$className = "\\modAI\\API\\$action";
if (class_exists($className) !== true) {
    \modAI\API\API::returnErrorFromAPIException(\modAI\Exceptions\APIException::notFound());
    exit;
}

$ajaxEndpoint = new $className($modx);
$ajaxEndpoint->handleRequest($request);

exit;
