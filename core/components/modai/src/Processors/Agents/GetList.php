<?php

namespace modAI\Processors\Agents;

use modAI\Model\Agent;
use modAI\Model\AgentContextProvider;
use modAI\Model\AgentTool;
use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOQuery;

class GetList extends GetListProcessor
{
    public $classKey = Agent::class;
    public $languageTopics = ['modai:default'];
    public $defaultSortField = 'name';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'modai.admin.agent';

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $toolId = $this->getProperty('tool_id');
        if (!empty($toolId)) {
            $c->leftJoin(AgentTool::class, 'AgentTools');
            $c->where([
                'AgentTools.tool_id' => $toolId
            ]);
        }

        $hideUsedTool = $this->getProperty('hideUsedTool', 0);
        if (!empty($hideUsedTool)) {
            $used = $this->modx->newQuery(AgentTool::class);
            $used->where([
                'tool_id' => $hideUsedTool
            ]);
            $used->select($this->modx->getSelectColumns(AgentTool::class, 'AgentTool', '', ['agent_id']));
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

        $contextProviderId = $this->getProperty('context_provider_id');
        if (!empty($contextProviderId)) {
            $c->leftJoin(AgentContextProvider::class, 'AgentContextProviders');
            $c->where([
                'AgentContextProviders.context_provider_id' => $contextProviderId
            ]);
        }

        $hideUsedContextProvider = $this->getProperty('hideUsedContextProvider', 0);
        if (!empty($hideUsedContextProvider)) {
            $used = $this->modx->newQuery(AgentContextProvider::class);
            $used->where([
                'context_provider_id' => $hideUsedContextProvider
            ]);
            $used->select($this->modx->getSelectColumns(AgentContextProvider::class, 'AgentContextProvider', '', ['agent_id']));
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
