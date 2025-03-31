<?php
namespace modAI\Model;

use xPDO\xPDO;

/**
 * Class ContextProvider
 *
 * @property string $class
 * @property string $name
 * @property string $description
 * @property array $properties
 *
 * @property \modAI\Model\AgentContextProvider[] $Agents
 *
 * @package modAI\Model
 */
class ContextProvider extends \xPDO\Om\xPDOSimpleObject
{
}
