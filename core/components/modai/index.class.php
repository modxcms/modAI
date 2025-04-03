<?php

use modAI\modAI;

abstract class ModAIBaseManagerController extends \MODX\Revolution\modExtraManagerController
{
    public modAI $modAI;

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


//        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'utils/utils.js');
        $this->addJavascript($this->modAI->getOption('mgrJsUrl') . 'utils/combos.js');
//        $this->addJavascript($this->modAI->getOption('jsUrl') . 'utils/fields.js');

        parent::initialize();
    }

    public function getLanguageTopics()
    {
        return ['modai:default'];
    }

    public function checkPermissions()
    {
        return true;
    }
}
