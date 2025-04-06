<?php

namespace modAI\Processors\Agents;

use modAI\Model\Agent;
use MODX\Revolution\Processors\Model\UpdateProcessor;

class Update extends UpdateProcessor
{
    public $classKey = Agent::class;
    public $languageTopics = ['modai:default'];
    public $objectType = 'modai.admin.agent';

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

        return parent::beforeSet();
    }
}
