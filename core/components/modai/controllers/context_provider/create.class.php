<?php

require_once dirname(__FILE__, 3) . '/index.class.php';

class ModAIContextProviderCreateManagerController extends ModAIBaseManagerController
{
    public function process(array $scriptProperties = [])
    {
    }

    public function getPageTitle()
    {
        return $this->modx->lexicon('modai.admin.context_provider.create');
    }

    public function loadCustomCssJs()
    {
        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'utils/combos.js');

        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'context_provider/panel.js');
        $this->addLastJavascript($this->modAI->getOption('mgrJsUrl') . 'context_provider/page.js');

        $this->addHtml('
        <script type="text/javascript">
            Ext.onReady(function() {
                MODx.load({ 
                    xtype: "modai-page-context_provider"
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
