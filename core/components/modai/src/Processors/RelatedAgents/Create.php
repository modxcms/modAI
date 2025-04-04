<?php

namespace modAI\Processors\RelatedAgents;

use modAI\Model\AgentContextProvider;
use modAI\Model\AgentTool;
use MODX\Revolution\Processors\ModelProcessor;

class Create extends ModelProcessor
{
    public $languageTopics = ['modai:default'];
    public $objectType = 'modai.admin.agent_tool';

    public function process()
    {
        $toolId = $this->getProperty('tool_id');
        $contextProviderId = $this->getProperty('context_provider_id');

        if (empty($contextProviderId) && empty($toolId)) {
            return $this->failure($this->modx->lexicon('modai.admin.error.related_agent_tool_context_provider_required'));
        }

        $data = [];

        if (!empty($toolId)) {
            $classKey = AgentTool::class;
            $data['tool_id'] = $toolId;
        } else {
            $classKey = AgentContextProvider::class;
            $data['context_provider_id'] = $contextProviderId;
        }

        $agents = $this->getProperty('agents');

        foreach ($agents as $agentId) {
            if (empty($agentId)) {
                continue;
            }
            $data['agent_id'] = $agentId;

            $agentTool = $this->modx->newObject($classKey);
            $agentTool->fromArray($data, '', true);
            $agentTool->save();
        }
    }
}
