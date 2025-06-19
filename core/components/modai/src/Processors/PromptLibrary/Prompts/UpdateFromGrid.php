<?php

namespace modAI\Processors\PromptLibrary\Prompts;

use modAI\Model\PromptLibraryPrompt;
use modAI\Utils;
use MODX\Revolution\Processors\Model\UpdateProcessor;

class UpdateFromGrid extends UpdateProcessor
{
    public $classKey = PromptLibraryPrompt::class;
    public $languageTopics = ['modai:default'];
    public $objectType = 'modai.admin.prompt_library.prompt';
    public $permission = 'modai_admin_prompt_library_prompt_save';

    private static $allowedKeys = ['id', 'name', 'enabled', 'public', 'rank'];

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

        $this->setProperty('enabled', Utils::convertToBoolean($this->getProperty('enabled')));

        $this->setProperty('public', Utils::convertToBoolean($this->getProperty('public')));
        if (!$this->modx->hasPermission($this->permission . '_public')) {
            $this->setProperty('public', false);
        }

        return parent::beforeSet();
    }
}
