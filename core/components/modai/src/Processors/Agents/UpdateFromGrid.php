<?php

namespace modAI\Processors\Agents;

use modAI\Model\Agent;
use MODX\Revolution\Processors\Model\UpdateProcessor;

class UpdateFromGrid extends UpdateProcessor
{
    public $classKey = Agent::class;
    public $languageTopics = ['modai:default'];
    public $objectType = 'modai.admin.agent';

    private static $allowedKeys = ['id', 'name', 'description', 'model', 'enabled'];

    public function initialize()
    {
        $data = $this->getProperty('data');
        if (empty($data)) {
            return $this->modx->lexicon('invalid_data');
        }
        $data = $this->modx->fromJSON($data);
        if (empty($data)) {
            return $this->modx->lexicon('invalid_data');
        }

        foreach (self::$allowedKeys as $key) {
            if (isset($data[$key])) {
                $this->setProperty($key, $data[$key]);
            }
        }

        $this->unsetProperty('data');

        return parent::initialize();
    }

    public function beforeSet()
    {
        $name = $this->getProperty('name');
        if (empty($name)) {
            $this->addFieldError('name', $this->modx->lexicon('modai.admin.error.required'));
            return false;
        }

        if ($this->doesAlreadyExist(['name' => $name, 'id:!=' => $this->object->id])) {
            $this->addFieldError('name', $this->modx->lexicon('modai.admin.error.agent_name_already_exists'));
            return false;
        }


        return parent::beforeSet();
    }
}
