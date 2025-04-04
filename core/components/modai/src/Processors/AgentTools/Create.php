<?php

namespace modAI\Processors\AgentTools;

use modAI\Model\AgentTool;
use MODX\Revolution\Processors\ModelProcessor;

class Create extends ModelProcessor
{
    public $classKey = AgentTool::class;
    public $languageTopics = ['modai:default'];
    public $objectType = 'modai.admin.agent_tool';

    public function process()
    {
        $agentId = $this->getProperty('agent_id');
        if (empty($agentId)) {
            return $this->failure($this->modx->lexicon('modai.admin.error.agent_id_required'));
        }

        $tools = $this->getProperty('tools');

        foreach ($tools as $toolId) {
            if (empty($toolId)) {
                continue;
            }

            $agentTool = $this->modx->newObject($this->classKey);
            $agentTool->set('agent_id', $agentId);
            $agentTool->set('tool_id', $toolId);
            $agentTool->save();
        }
    }
}
