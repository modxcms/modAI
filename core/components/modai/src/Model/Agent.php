<?php
namespace modAI\Model;

use xPDO\xPDO;

/**
 * Class Agent
 *
 * @property string $name
 * @property string $description
 * @property string $prompt
 * @property string $model
 * @property null | array $advanced_config
 * @property null | array $user_groups
 * @property boolean $enabled
 *
 * @property \modAI\Model\AgentTool[] $AgentTools
 *
 * @package modAI\Model
 */
class Agent extends \xPDO\Om\xPDOSimpleObject
{
}
