<?php

require_once dirname(__FILE__, 3) . '/index.class.php';

class ModAIAgentCreateManagerController extends ModAIBaseManagerController
{
    public function process(array $scriptProperties = [])
    {
    }

    public function getPageTitle()
    {
        return $this->modx->lexicon('modai.admin.agent.create');
    }

    public function loadCustomCssJs()
    {
        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'utils/combos.js');
        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'utils/acl_grid.js');

        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'agent/advanced_config.grid.js');

        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'agent/panel.js');
        $this->addLastJavascript($this->modAI->getOption('mgrJsUrl') . 'agent/page.js');

        $this->addHtml('
        <script type="text/javascript">
            Ext.onReady(function() {
                MODx.load({ 
                    xtype: "modai-page-agent",
                    permissions: ' . json_encode($this->permissions) . '
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
