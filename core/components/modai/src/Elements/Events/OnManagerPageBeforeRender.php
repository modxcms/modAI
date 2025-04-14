<?php

namespace modAI\Elements\Events;

class OnManagerPageBeforeRender extends Event
{

    public function run()
    {
        $action = '';

        if (isset($this->modx->controller) && is_object($this->modx->controller) && property_exists($this->modx->controller, 'action')) {
            $action = $this->modx->controller->action;
        } elseif (isset($_REQUEST['a'])) {
            $action = $_REQUEST['a'];
        }

        if (!in_array($action, ['resource/create', 'resource/update'])) {
            return;
        }

        foreach ($this->modAI->getUILexiconTopics() as $topic) {
            $this->modx->controller->addLexiconTopic($topic);
        }

        $baseConfig = $this->modAI->getBaseConfig();
        $this->modx->controller->addHtml('
                <script type="text/javascript">
                if (typeof modAI === "undefined") {
                    let modAI;
                    Ext.onReady(function() {
                        modAI = ModAI.init(' . json_encode($baseConfig) . ');
                        
                         Ext.defer(function () {
                           modAI.initOnResource({
                              tvs:  ' . $this->modx->toJSON($this->modAI->getListOfTVsWithIDs()) . ',
                              resourceFields:  ' . $this->modx->toJSON($this->modAI->getResourceFields()) . ',
                            });
                         }, 500);
                    });
                }
                </script>
            ');

        $this->modx->regClientStartupScript($this->modAI->getJSFile());
    }
}
