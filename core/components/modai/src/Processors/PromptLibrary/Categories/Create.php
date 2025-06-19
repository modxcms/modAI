<?php

namespace modAI\Processors\PromptLibrary\Categories;

use modAI\Model\Agent;
use modAI\Model\PromptLibraryCategory;
use modAI\Utils;
use MODX\Revolution\Processors\Model\CreateProcessor;

class Create extends CreateProcessor
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

        $type = $this->getProperty('type');
        if (empty($type)) {
            $this->addFieldError('type', $this->modx->lexicon('modai.admin.error.required'));
            return false;
        }

        $parent = (int)$this->getProperty('parent_id');
        if (empty($parent)) {
            $this->addFieldError('parent_id', $this->modx->lexicon('modai.admin.error.required'));
            return false;
        }

        $c = $this->modx->newQuery($this->classKey);
        $c->where([
            'parent_id' => $parent
        ]);
        $c->sortby('rank', 'DESC');
        $c->limit(1);
        $items = $this->modx->getCollection($this->classKey, $c);

        $rank = 0;
        foreach ($items as $item) {
            $rank = $item->get('rank') + 1;
            break;
        }

        $this->setProperty('rank', $rank);

        $this->setProperty('enabled', Utils::convertToBoolean($this->getProperty('enabled')));
        $this->setProperty('created_by', $this->modx->user->id);

        $this->setProperty('public', Utils::convertToBoolean($this->getProperty('public')));
        if (!$this->modx->hasPermission($this->permission . '_public')) {
            $this->setProperty('public', false);
        }

        return parent::beforeSet();
    }
}
