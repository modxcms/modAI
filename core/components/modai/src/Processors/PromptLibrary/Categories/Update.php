<?php

namespace modAI\Processors\PromptLibrary\Categories;

use modAI\Model\PromptLibraryCategory;
use modAI\Utils;
use MODX\Revolution\Processors\Model\UpdateProcessor;

class Update extends UpdateProcessor
{
    public $classKey = PromptLibraryCategory::class;
    public $languageTopics = ['modai:default'];
    public $objectType = 'modai.admin.prompt_library.category';
    public $permission = 'modai_admin_prompt_library_category_save';

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
