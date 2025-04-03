<?php

require_once dirname(__FILE__, 3) . '/index.class.php';

class ModAIToolCreateManagerController extends ModAIBaseManagerController
{
    public function process(array $scriptProperties = [])
    {
    }

    public function getPageTitle()
    {
        return $this->modx->lexicon('modai.admin.tool.create');
    }

    public function loadCustomCssJs()
    {
        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'utils/combos.js');

        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'tool/panel.js');
        $this->addLastJavascript($this->modAI->getOption('mgrJsUrl') . 'tool/page.js');

        $this->addHtml('
        <script type="text/javascript">
            Ext.onReady(function() {
                MODx.load({ 
                    xtype: "modai-page-tool"
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
