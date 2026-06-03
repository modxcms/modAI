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

        $modelsSet = false;

        switch ($this->action) {
            case \xPDO\Transport\xPDOTransport::ACTION_INSTALL:

                foreach (['openai-key', 'anthropic-key', 'google-key', 'openrouter-key'] as $key) {
                    if (isset($options[$key]) && !empty($options[$key]) && ($options[$key] !== 'filled')) {
                        $settingObject = $this->modx->getObject(modSystemSetting::class, ['key' => 'modai.api.' . str_replace('-', '.', $key)]);

                        if ($settingObject) {
                            $settingObject->set('value', $options[$key]);
                            $settingObject->save();

                            if (!$modelsSet) {
                                $modelsSet = true;
                                $this->setDefaultModels(substr($key, 0, -4));
                            }
                        }
                    }
                }
                break;
            case \xPDO\Transport\xPDOTransport::ACTION_UPGRADE:
            case \xPDO\Transport\xPDOTransport::ACTION_UNINSTALL:
                break;
        }

        return true;
    }

    private function setDefaultModels($service)
    {
        switch ($service) {
            case 'anthropic':
                $models = ['text' => 'claude-opus-4-7', 'vision' => 'claude-haiku-4-5', 'image' => '', 'title' => 'claude-haiku-4-5'];
                break;
            case 'google':
                $models = ['text' => 'gemini-3.5-flash', 'vision' => 'gemini-3.1-flash-lite', 'image' => 'gemini-3.1-flash-image-preview', 'title' => 'gemini-2.5-flash-lite'];
                break;
            case 'openrouter':
                $models = ['text' => 'openrouter/openai/gpt-chat-latest', 'vision' => 'openrouter/openai/gpt-5-nano', 'image' => 'openrouter/openai/gpt-5.4-image-2', 'title' => 'openrouter/openai/gpt-5-nano'];
                break;
            default:
                $models = ['text' => 'openai/gpt-5.5', 'vision' => 'openai/gpt-5-nano', 'image' => 'openai/gpt-image-2', 'title' => 'openai/gpt-5-nano'];
                break;
        }

        if (!empty($models['text'])) {
            $text = $this->modx->getObject(modSystemSetting::class, ['key' => 'modai.global.text.model')]);
            if ($text) {
                $text->set('value', $models['text']);
                $text->save();
            }
        }

        if (!empty($models['vision'])) {
            $vision = $this->modx->getObject(modSystemSetting::class, ['key' => 'modai.global.vision.model')]);
            if ($vision) {
                $vision->set('value', $models['vision']);
                $text->save();
            }
        }

        if (!empty($models['image'])) {
            $image = $this->modx->getObject(modSystemSetting::class, ['key' => 'modai.global.image.model')]);
            if ($image) {
                $image->set('value', $models['text']);
                $image->save();
            }
        }

        if (!empty($models['title'])) {
            $title = $this->modx->getObject(modSystemSetting::class, ['key' => 'modai.chat.title.model')]);
            if ($title) {
                $title->set('value', $models['title']);
                $title->save();
            }
        }
    }
};
