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

        $enabled = $this->getProperty('enabled', '');
        if ($enabled !== '') {
            $c->where([
                'enabled' => $enabled,
            ]);
        }

        $search = $this->getProperty('search', '');
        if (!empty($search)) {
            $c->where(['name:LIKE' => "%{$search}%"]);
        }

        return parent::prepareQueryBeforeCount($c);
    }
}
