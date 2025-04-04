<?php

namespace modAI\Processors\RelatedAgents;

use modAI\Model\AgentContextProvider;
use modAI\Model\AgentTool;
use MODX\Revolution\modAccessibleObject;
use MODX\Revolution\Processors\Model\RemoveProcessor;

class Remove extends RemoveProcessor
{
    public $languageTopics = ['modai:default'];
    public $objectType = 'modai.admin.agent_tool';

    public function initialize()
    {
        $agentId = $this->getProperty('agent_id');
        $toolId = $this->getProperty('tool_id');
        $contextProviderId = $this->getProperty('context_provider_id');

        if (empty($contextProviderId) && empty($toolId)) {
            return $this->failure($this->modx->lexicon('modai.admin.error.related_agent_tool_context_provider_required'));
        }

        $where = [
            'agent_id' => $agentId,
        ];

        if (!empty($toolId)) {
            $classKey = AgentTool::class;
            $where['tool_id'] = $toolId;
        } else {
            $classKey = AgentContextProvider::class;
            $where['context_provider_id'] = $contextProviderId;
        }

        $this->object = $this->modx->getObject($classKey, $where);
        if (empty($this->object)) {
            return $this->modx->lexicon('modai.admin.error.related_agent_not_found');
        }

        if ($this->checkRemovePermission && $this->object instanceof modAccessibleObject && !$this->object->checkPolicy('remove')) {
            return $this->modx->lexicon('access_denied');
        }

        return true;
    }
}
