<?php

namespace modAI\Processors\Tools;

use modAI\Model\Tool;
use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOQuery;

class GetList extends GetListProcessor
{
    public $classKey = Tool::class;
    public $languageTopics = ['modai:default'];
    public $defaultSortField = 'name';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'modai.admin.tool';

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $id = (int)$this->getProperty('id', 0);
        if (!empty($id)) {
            $c->where(['id' => $id]);
        }

        $enabled = $this->getProperty('enabled', '');
        if ($enabled !== '') {
            $c->where([
                'enabled' => $enabled,
            ]);
        }

        $default = $this->getProperty('default', '');
        if ($default !== '') {
            $c->where([
                'default' => $default,
            ]);
        }

        $search = $this->getProperty('search', '');
        if (!empty($search)) {
            $c->where(['name:LIKE' => "%{$search}%"]);
        }

        return parent::prepareQueryBeforeCount($c);
    }
}
