<?php

namespace modAI\Processors\ContextProviders;

use modAI\Model\AgentContextProvider;
use modAI\Model\ContextProvider;
use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOQuery;

class GetList extends GetListProcessor
{
    public $classKey = ContextProvider::class;
    public $languageTopics = ['modai:default'];
    public $defaultSortField = 'name';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'modai.admin.context_provider';

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $agent = $this->getProperty('agent');
        if (!empty($agent)) {
            $c->leftJoin(AgentContextProvider::class, 'AgentContextProviders');
            $c->where([
                'AgentContextProviders.agent_id' => $agent
            ]);
        }

        $hideUsed = $this->getProperty('hideUsed', 0);
        if (!empty($hideUsed)) {
            $used = $this->modx->newQuery(AgentContextProvider::class);
            $used->where([
                'agent_id' => $hideUsed
            ]);
            $used->select($this->modx->getSelectColumns(AgentContextProvider::class, 'AgentContextProvider', '', ['context_provider_id']));
            $used->prepare();
            $used->stmt->execute();

            $used = $used->stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
            $used = array_map('intval', $used);

            if (!empty($used)) {
                $c->where([
                    'id:NOT IN' => $used
                ]);
            }
        }

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

        $search = $this->getProperty('search', '');
        if (!empty($search)) {
            $c->where(['name:LIKE' => "%{$search}%"]);
        }

        return parent::prepareQueryBeforeCount($c);
    }
}
