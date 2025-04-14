<?php

use modAI\modAI;

abstract class ModAIBaseManagerController extends \MODX\Revolution\modExtraManagerController
{
    public modAI $modAI;

    protected array $permissions;

    public function initialize()
    {
        if (!$this->modx->services->has('modai')) {
            return;
        }

        /** @var modAI | null $modAI */
        $modAI = $this->modx->services->get('modai');

        if ($modAI === null) {
            return;
        }

        $this->modAI = $modAI;


        $this->addCss($this->modAI->getOption('mgrCssUrl') . 'modai.css');
        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'modai.js');

        $this->addHtml('<script type="text/javascript">
            Ext.onReady(function() {
                modAIAdmin.config = ' . $this->modx->toJSON($this->modAI->config) . ';
            });
        </script>');


        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'utils/combos.js');

        $this->loadPermissions();

        parent::initialize();
    }

    public function getLanguageTopics()
    {
        return ['modai:default'];
    }

    protected function loadPermissions()
    {
        $this->permissions = $this->modAI->getAdminPermissions();
    }

    public function checkPermissions()
    {
        return $this->modx->hasPermission('modai_admin');
    }
}
