<?php

namespace modAI\Processors\PromptLibrary\Categories;

use modAI\Model\PromptLibraryCategory;
use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOQuery;

class GetList extends GetListProcessor
{
    public $classKey = PromptLibraryCategory::class;
    public $languageTopics = ['modai:default'];
    public $defaultSortField = 'rank';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'modai.admin.prompt_library.category';
    public $permission = 'modai_admin_prompt_library';

    public function getData()
    {
        $data = [];
        $limit = (int)$this->getProperty('limit');
        $start = (int)$this->getProperty('start');

        /* query for chunks */
        $c = $this->modx->newQuery($this->classKey);
        $c = $this->prepareQueryBeforeCount($c);
        $data['total'] = $this->listTotal ?: $this->modx->getCount($this->classKey, $c);
        $c = $this->prepareQueryAfterCount($c);

        $c->sortby('parent_id', 'ASC');
        $c->sortby('rank', 'ASC');

        if ($limit > 0) {
            $c->limit($limit, $start);
        }

        $data['results'] = $this->modx->getCollection($this->classKey, $c);

        return $data;
    }

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $id = (int)$this->getProperty('id', 0);
        if (!empty($id)) {
            $c->where(['id' => $id]);
        }

        $search = $this->getProperty('search', '');
        if (!empty($search)) {
            $c->where(['name:LIKE' => "%{$search}%"]);
        }

        return parent::prepareQueryBeforeCount($c);
    }
}
