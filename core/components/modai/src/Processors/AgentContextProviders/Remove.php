<?php

namespace modAI\Processors\AgentContextProviders;

use modAI\Model\AgentContextProvider;
use MODX\Revolution\modAccessibleObject;
use MODX\Revolution\Processors\Model\RemoveProcessor;

class Remove extends RemoveProcessor
{
    public $classKey = AgentContextProvider::class;
    public $languageTopics = ['modai:default'];
    public $objectType = 'modai.admin.agent_context_provider';
    public $permission = 'modai_admin_agent_context_provider_delete';

    public function initialize()
    {
        $agentId = $this->getProperty('agent_id');
        $contextProviderId = $this->getProperty('context_provider_id');

        $this->object = $this->modx->getObject($this->classKey, ['agent_id' => $agentId, 'context_provider_id' => $contextProviderId]);
        if (empty($this->object)) {
            return $this->modx->lexicon('modai.admin.error.agent_context_provider_not_found');
        }

        if ($this->checkRemovePermission && $this->object instanceof modAccessibleObject && !$this->object->checkPolicy('remove')) {
            return $this->modx->lexicon('access_denied');
        }

        return true;
    }
}
