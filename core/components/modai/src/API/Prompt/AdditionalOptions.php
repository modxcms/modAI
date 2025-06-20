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
        if (!empty($definedAdditionalControls[$type])) {
            foreach ($definedAdditionalControls[$type] as $option) {
                $allowedOptions[$option['name']] = array_flip(array_keys($option['values']));
            }
        }

        $verifiedOptions = [];
        foreach ($additionalOptions as $key => $value) {
            if (!isset($allowedOptions[$key]) || !isset($allowedOptions[$key][$value])) {
                continue;
            }

            if ($key === 'model' && !empty($value)) {
                if ($type === 'image') {
                    Settings::setImageSetting($this->modx, $field, 'model', $value);
                } else {
                    Settings::setTextSetting($this->modx, $field, 'model', $value);
                }
                continue;
            }

            $verifiedOptions[$key] = $value;
        }

        return $verifiedOptions;
    }
}
