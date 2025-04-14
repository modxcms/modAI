<?php

namespace modAI\Processors\Agents;

use modAI\Model\Agent;
use MODX\Revolution\Processors\Model\UpdateProcessor;

class Update extends UpdateProcessor
{
    public $classKey = Agent::class;
    public $languageTopics = ['modai:default'];
    public $objectType = 'modai.admin.agent';
    public $permission = 'modai_admin_agent_save';

    public function beforeSet()
    {

        $name = $this->getProperty('name');
        if (empty($name)) {
            $this->addFieldError('name', $this->modx->lexicon('modai.admin.error.required'));
            return false;
        }

        if ($this->doesAlreadyExist(['name' => $name, 'id:!=' => $this->object->id])) {
            $this->addFieldError('name', $this->modx->lexicon('modai.admin.error.context_provider_name_already_exists'));
            return false;
        }

        $advancedConfig = $this->getProperty('advanced_config');
        $advancedConfig = json_decode($advancedConfig, true);
        if (empty($advancedConfig)) {
            $this->setProperty('advanced_config', null);
        }

        $userGroups = $this->getProperty('user_groups');
        $userGroups = array_filter($userGroups);
        if (empty($userGroups)) {
            $this->setProperty('user_groups', null);
        }

        return parent::beforeSet();
    }
}
