<?php
namespace modAI\Model;

use xPDO\xPDO;

/**
 * Class Agent
 *
 * @property string $name
 * @property string $description
 * @property string $prompt
 * @property boolean $enabled
 *
 * @property \modAI\Model\AgentTool[] $AgentTools
 *
 * @package modAI\Model
 */
class Agent extends \xPDO\Om\xPDOSimpleObject
{
}
