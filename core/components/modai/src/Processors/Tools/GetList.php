<?php

namespace modAI\Processors\Tools;

use modAI\Model\AgentTool;
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
    public $permission = 'modai_admin_tools';

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $agent = $this->getProperty('agent');
        if (!empty($agent)) {
            $c->leftJoin(AgentTool::class, 'AgentTools');
            $c->where([
                'AgentTools.agent_id' => $agent
            ]);
        }

        $hideUsed = $this->getProperty('hideUsed', 0);
        if (!empty($hideUsed)) {
            $used = $this->modx->newQuery(AgentTool::class);
            $used->where([
                'agent_id' => $hideUsed
            ]);
            $used->select($this->modx->getSelectColumns(AgentTool::class, 'AgentTool', '', ['tool_id']));
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
