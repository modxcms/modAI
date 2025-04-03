<?php

require_once dirname(__FILE__, 3) . '/index.class.php';

class ModAIToolUpdateManagerController extends ModAIBaseManagerController
{
    private array $toolData;

    public function process(array $scriptProperties = [])
    {
        $id = (int)$this->scriptProperties['id'];
        if (empty($id)) {
            $this->failure($this->modx->lexicon('modai.admin.error.tool_not_found'));
            return;
        }

        $tool = $this->modx->getObject(\modAI\Model\Tool::class, ['id' => $id]);
        if (!$tool) {
            $this->failure($this->modx->lexicon('modai.admin.error.tool_not_found'));
            return;
        }

        $this->toolData = $tool->toArray();
        $this->toolData['classConfig'] = $this->toolData['class']::getConfig();
    }

    public function getPageTitle()
    {
        return $this->modx->lexicon('modai.admin.tool.update');
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
                    xtype: "modai-page-tool",
                    record: ' . $this->modx->toJSON($this->toolData) . '
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
