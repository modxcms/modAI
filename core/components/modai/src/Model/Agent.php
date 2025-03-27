<?php

namespace modAI\Model;

use xPDO\xPDO;

/**
 * Class Agent
 *
 * @property boolean $enabled
 * @property string $name
 * @property string $description
 * @property string $model
 * @property string $prompt
 *
 * @property \modAI\Model\AgentContextProvider[] $ContextProviders
 *
 * @package modAI\Model
 */
class Agent extends \xPDO\Om\xPDOSimpleObject
{
}
