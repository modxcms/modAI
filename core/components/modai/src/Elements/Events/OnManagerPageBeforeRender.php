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



        foreach ($this->modAI->getUILexiconTopics() as $topic) {
            $this->modx->controller->addLexiconTopic($topic);
        }

        $this->modx->regClientStartupScript($this->modAI->getJSFile());

        $baseConfig = $this->modAI->getBaseConfig();
        $this->modx->controller->addHtml('
                <script type="text/javascript">
                if (typeof modAI === "undefined") {
                    Ext.onReady(function() {
                        const modAI = ModAI.init(' . json_encode($baseConfig) . ');
                        
                        modAI.initGlobalButton();
                        
                        window.modAI = modAI;
                    });
                }
                </script>
            ');


        if (!in_array($action, ['resource/create', 'resource/update'])) {
            return;
        }

        $this->modx->controller->addHtml('
            <script type="text/javascript">
            Ext.onReady(function() {
                if (typeof modAI !== "undefined") {
                    Ext.defer(() => {
                        modAI.initOnResource({
                          tvs:  ' . $this->modx->toJSON($this->modAI->getListOfTVsWithIDs()) . ',
                          resourceFields:  ' . $this->modx->toJSON($this->modAI->getResourceFields()) . ',
                        });
                    }, 500);
                }
            });
            </script>
        ');
    }
}
