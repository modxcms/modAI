<?php

require_once dirname(__FILE__, 3) . '/index.class.php';

class ModAIAgentUpdateManagerController extends ModAIBaseManagerController
{
    private array $agentData;

    public function process(array $scriptProperties = [])
    {
        $id = (int)$this->scriptProperties['id'];
        if (empty($id)) {
            $this->failure($this->modx->lexicon('modai.admin.error.agent_not_found'));
            return;
        }

        $agent = $this->modx->getObject(\modAI\Model\Agent::class, ['id' => $id]);
        if (!$agent) {
            $this->failure($this->modx->lexicon('modai.admin.error.agent_not_found'));
            return;
        }

        $this->agentData = $agent->toArray();
    }

    public function getPageTitle()
    {
        return $this->modx->lexicon('modai.admin.agent.update');
    }

    public function loadCustomCssJs()
    {
        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'utils/combos.js');

        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'agent/agent_tools.grid.js');
        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'agent/agent_tools.window.js');

        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'agent/agent_context_providers.grid.js');
        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'agent/agent_context_providers.window.js');

        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'agent/panel.js');
        $this->addLastJavascript($this->modAI->getOption('mgrJsUrl') . 'agent/page.js');

        $this->addHtml('
        <script type="text/javascript">
            Ext.onReady(function() {
                MODx.load({ 
                    xtype: "modai-page-agent",
                    record: ' . $this->modx->toJSON($this->agentData) . '
                });
            });
        </script>
        ');
    }

    public function getTemplateFile()
    {
        return $this->modAI->getOption('templatesPath') . 'default.tpl';
    }

    public function checkPermissions()
    {
        return parent::checkPermissions();
    }
}
