<?php

namespace modAI\Processors\AgentTools;

use modAI\Model\AgentTool;
use MODX\Revolution\modAccessibleObject;
use MODX\Revolution\Processors\Model\RemoveProcessor;

class Remove extends RemoveProcessor
{
    public $classKey = AgentTool::class;
    public $languageTopics = ['modai:default'];
    public $objectType = 'modai.admin.agent_tool';

    public function initialize()
    {
        $agentId = $this->getProperty('agent_id');
        $toolId = $this->getProperty('tool_id');

        $this->object = $this->modx->getObject($this->classKey, ['agent_id' => $agentId, 'tool_id' => $toolId]);
        if (empty($this->object)) {
            return $this->modx->lexicon('modai.admin.error.agent_tool_not_found');
        }

        if ($this->checkRemovePermission && $this->object instanceof modAccessibleObject && !$this->object->checkPolicy('remove')) {
            return $this->modx->lexicon('access_denied');
        }

        return true;
    }
}
