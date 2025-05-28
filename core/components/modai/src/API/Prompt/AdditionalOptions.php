<?php

namespace modAI\API\Prompt;

use modAI\Settings;

trait AdditionalOptions {
    protected function getAdditionalOptions($data, $field, $type): array
    {
        $additionalOptions = $this->modx->getOption('additionalOptions', $data, null);

        if (empty($additionalOptions) || !is_array($additionalOptions)) {
            return [];
        }

        $definedAdditionalControls = $this->modx->getOption('modai.chat.additional_controls');
        if (!empty($definedAdditionalControls)) {
            $definedAdditionalControls = json_decode($definedAdditionalControls, true);
        }

        if (empty($definedAdditionalControls)) {
            return [];
        }

        $allowedOptions = [];
        if (!empty($definedAdditionalControls['text'])) {
            foreach ($definedAdditionalControls['text'] as $option) {
                $allowedOptions[$option['name']] = array_flip(array_keys($option['values']));
            }
        }

        $verifiedOptions = [];
        foreach ($additionalOptions as $key => $value) {
            if (!isset($allowedOptions[$key]) || !isset($allowedOptions[$key][$value])) {
                continue;
            }

            if (!empty($additionalOptions['model'])) {
                if ($type === 'image') {
                    Settings::setImageSetting($this->modx, $field, 'model', $additionalOptions['model']);
                } else {
                    Settings::setTextSetting($this->modx, $field, 'model', $additionalOptions['model']);
                }
                continue;
            }

            $verifiedOptions[$key] = $value;
        }

        return $verifiedOptions;
    }
}
