<?php
namespace modAI;

use MODX\Revolution\modX;

class RequiredSettingException extends \Exception {
    public function __construct(string $setting)
    {
        parent::__construct("System setting `$setting` is required.");
    }
}

class Settings {
    /**
     * @throws RequiredSettingException
     */
    public static function getFieldSetting(modX $modx, string $field, string $setting): string {
        $value = $modx->getOption("modai.$field.$setting", null, $modx->getOption("modai.global.$setting"), true);

        if (empty($value)) {
            throw new RequiredSettingException("modai.global.$setting");
        }

        return $value;
    }

    public static function getPrompt(modX $modx, string $field, string $model = null): string {
        return $modx->getOption("modai.$field.prompt");
    }

    public static function getSetting(modX $modx, string $key, string $default = null) {
        return $modx->getOption("modai.$key", null, $default);
    }
}
