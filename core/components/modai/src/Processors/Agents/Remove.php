<?php

namespace modAI\Processors\Agents;

use modAI\Model\Agent;
use MODX\Revolution\Processors\Model\RemoveProcessor;

class Remove extends RemoveProcessor
{
    public $classKey = Agent::class;
    public $languageTopics = ['modai:default'];
    public $objectType = 'modai.admin.agent';
    public $permission = 'modai_admin_agent_delete';
}
