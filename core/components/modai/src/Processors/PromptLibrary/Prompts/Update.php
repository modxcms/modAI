<?php

namespace modAI\Processors\PromptLibrary\Prompts;

use modAI\Model\PromptLibraryPrompt;
use modAI\Utils;
use MODX\Revolution\Processors\Model\UpdateProcessor;

class Update extends UpdateProcessor
{
    public $classKey = PromptLibraryPrompt::class;
    public $languageTopics = ['modai:default'];
    public $objectType = 'modai.admin.prompt_library.prompt';
    public $permission = 'modai_admin_prompt_library_prompt_save';

    public function beforeSet()
    {
        $name = $this->getProperty('name');
        if (empty($name)) {
            $this->addFieldError('name', $this->modx->lexicon('modai.admin.error.required'));
            return false;
        }

        $category = (int)$this->getProperty('category_id');
        if (empty($category)) {
            $this->addFieldError('category_id', $this->modx->lexicon('modai.admin.error.required'));
            return false;
        }

        $prompt = $this->getProperty('prompt');
        if (empty($prompt)) {
            $this->addFieldError('prompt', $this->modx->lexicon('modai.admin.error.required'));
            return false;
        }

        $this->setProperty('enabled', Utils::convertToBoolean($this->getProperty('enabled')));

        return parent::beforeSet();
    }
}
