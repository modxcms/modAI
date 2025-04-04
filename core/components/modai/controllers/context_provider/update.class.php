<?php

require_once dirname(__FILE__, 3) . '/index.class.php';

class ModAIContextProviderUpdateManagerController extends ModAIBaseManagerController
{
    private array $contextProviderData;

    public function process(array $scriptProperties = [])
    {
        $id = (int)$this->scriptProperties['id'];
        if (empty($id)) {
            $this->failure($this->modx->lexicon('modai.admin.error.context_provider_not_found'));
            return;
        }

        $contextProvider = $this->modx->getObject(\modAI\Model\ContextProvider::class, ['id' => $id]);
        if (!$contextProvider) {
            $this->failure($this->modx->lexicon('modai.admin.error.context_provider_not_found'));
            return;
        }

        $this->contextProviderData = $contextProvider->toArray();
        $this->contextProviderData['classConfig'] = $this->contextProviderData['class']::getConfig();
    }

    public function getPageTitle()
    {
        return $this->modx->lexicon('modai.admin.context_provider.update');
    }

    public function loadCustomCssJs()
    {
        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'utils/combos.js');

        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'related_agents/grid.js');
        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'related_agents/window.js');

        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'context_provider/panel.js');
        $this->addLastJavascript($this->modAI->getOption('mgrJsUrl') . 'context_provider/page.js');

        $this->addHtml('
        <script type="text/javascript">
            Ext.onReady(function() {
                MODx.load({ 
                    xtype: "modai-page-context_provider",
                    record: ' . $this->modx->toJSON($this->contextProviderData) . '
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
