<?php

use MODX\Revolution\modSystemSetting;

return new class() {
    /**
     * @var \MODX\Revolution\modX
     */
    private $modx;

    /**
     * @var int
     */
    private $action;

    /**
    * @param \MODX\Revolution\modX $modx
    * @param int $action
    * @param array $options
    * @return bool
    */
    public function __invoke(&$modx, $action, $options, $object)
    {
        $this->modx =& $modx;
        $this->action = $action;

        switch ($this->action) {
            case \xPDO\Transport\xPDOTransport::ACTION_INSTALL:
            case \xPDO\Transport\xPDOTransport::ACTION_UPGRADE:

                foreach (['openai-key', 'anthropic-key', 'google-key', 'openrouter-key'] as $key) {
                    if (isset($options[$key]) && !empty($options[$key]) && ($options[$key] !== 'filled')) {
                        $settingObject = $this->modx->getObject(modSystemSetting::class, ['key' => 'modai.api.' . str_replace('-', '.', $key)]);

                        if ($settingObject) {
                            $settingObject->set('value', $options[$key]);
                            $settingObject->save();
                        }
                    }
                }
                break;
            case \xPDO\Transport\xPDOTransport::ACTION_UNINSTALL:
                break;
        }

        return true;
    }
};
