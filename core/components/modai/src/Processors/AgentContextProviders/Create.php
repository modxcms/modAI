<?php

namespace modAI\Processors\AgentContextProviders;

use modAI\Model\AgentContextProvider;
use MODX\Revolution\Processors\ModelProcessor;

class Create extends ModelProcessor
{
    public $classKey = AgentContextProvider::class;
    public $languageTopics = ['modai:default'];
    public $objectType = 'modai.admin.agent_context_provider';
    public $permission = 'modai_admin_agent_context_provider_save';

    public function process()
    {
        $agentId = $this->getProperty('agent_id');
        if (empty($agentId)) {
            return $this->failure($this->modx->lexicon('modai.admin.error.agent_id_required'));
        }

        $contextProviders = $this->getProperty('context_providers');

        foreach ($contextProviders as $contextProviderId) {
            if (empty($contextProviderId)) {
                continue;
            }

            $agentContextProvider = $this->modx->newObject($this->classKey);
            $agentContextProvider->set('agent_id', $agentId);
            $agentContextProvider->set('context_provider_id', $contextProviderId);
            $agentContextProvider->save();
        }
    }
}
