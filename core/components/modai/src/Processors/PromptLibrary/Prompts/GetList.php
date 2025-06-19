<?php

namespace modAI\Processors\PromptLibrary\Prompts;

use modAI\Model\PromptLibraryPrompt;
use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOQuery;

class GetList extends GetListProcessor
{
    public $classKey = PromptLibraryPrompt::class;
    public $languageTopics = ['modai:default'];
    public $defaultSortField = 'rank';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'modai.admin.prompt_library.prompt';
    public $permission = 'modai_admin_prompt_library';

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $id = (int)$this->getProperty('id', 0);
        if (!empty($id)) {
            $c->where(['id' => $id]);
        }

        $category = (int)$this->getProperty('category', 0);
        if (!empty($category)) {
            $c->where(['category_id' => $category]);
        }

        $type = $this->getProperty('type', '');
        if (!empty($type)) {
            $c->where(['type' => $type]);
        }

        $enabled = $this->getProperty('enabled', '');
        if ($enabled !== '') {
            $c->where([
                'enabled' => $enabled,
            ]);
        }

        $public = $this->getProperty('public', '');
        if ($public === '') {
            $c->where([
                'public' => true,
                'OR:created_by:=' => $this->modx->user->id,
            ]);
        } else {
            $public = (int)$public;
            if ($public === 0) {
                $c->where([
                    'public' => false,
                    'created_by' => $this->modx->user->id,
                ]);
            } else {
                $c->where([
                    'public' => true
                ]);
            }
        }

        $search = $this->getProperty('search', '');
        if (!empty($search)) {
            $c->where(['name:LIKE' => "%{$search}%"]);
        }

        return parent::prepareQueryBeforeCount($c);
    }
}
