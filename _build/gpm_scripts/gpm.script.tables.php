<?php
use xPDO\Transport\xPDOTransport;

/**
 * Create tables
 *
 * THIS SCRIPT IS AUTOMATICALLY GENERATED, NO CHANGES WILL APPLY
 *
 * @package modai
 * @subpackage build.scripts
 *
 * @var \xPDO\Transport\xPDOTransport $transport
 * @var array $object
 * @var array $options
 */

$modx =& $transport->xpdo;

if ($options[xPDOTransport::PACKAGE_ACTION] === xPDOTransport::ACTION_UNINSTALL) return true;

$manager = $modx->getManager();

$manager->createObjectContainer(modAI\Model\Tool::class);
$manager->createObjectContainer(modAI\Model\Agent::class);
$manager->createObjectContainer(modAI\Model\AgentTool::class);
$manager->createObjectContainer(modAI\Model\ContextProvider::class);
$manager->createObjectContainer(modAI\Model\AgentContextProvider::class);

return true;
