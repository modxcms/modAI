<?php

require_once dirname(__FILE__, 2) . '/index.class.php';

class ModAIHomeManagerController extends ModAIBaseManagerController
{
    protected $permissions = [];

    public function process(array $scriptProperties = [])
    {
    }

    public function getPageTitle()
    {
        return $this->modx->lexicon('modai.admin.home.page_title');
    }

    public function loadCustomCssJs()
    {
        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'home/widgets/agents.grid.js');
        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'home/widgets/tools.grid.js');
        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'home/widgets/context_providers.grid.js');

        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'home/panel.js');
        $this->addLastJavascript($this->modAI->getOption('mgrJsUrl') . 'home/page.js');

        $this->addHtml('
        <script type="text/javascript">
            Ext.onReady(function() {
                MODx.load({ 
                    xtype: "modai-page-home",
                });
            });
        </script>
        ');
    }

    public function getTemplateFile()
    {
        return $this->modAI->getOption('templatesPath') . 'default.tpl';
    }
}
