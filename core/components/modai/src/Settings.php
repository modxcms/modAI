<?php
namespace modAI;

use modAI\Exceptions\RequiredSettingException;
use MODX\Revolution\modX;

class Settings {
    private static function getOption(modX $modx, string $namespace, string $field, string $area, string $setting): ?string
    {
        if (!empty($field)) {
            $value = $modx->getOption("$namespace.$field.$area.$setting");
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        $value = $modx->getOption("$namespace.global.$area.$setting");
        if ($value !== null && $value !== '') {
            return $value;
        }

        if ($namespace === 'modai') {
            return null;
        }

        if (!empty($field)) {
            $value = $modx->getOption("modai.$field.$area.$setting");
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        $value = $modx->getOption("modai.global.$area.$setting");
        if ($value !== null && $value !== '') {
            return $value;
        }

        return null;
    }

    /**
     * @throws RequiredSettingException
     */
    public static function getTextSetting(modX $modx, string $field, string $setting, string $namespace = 'modai', bool $required = true): ?string
    {
        $value = self::getOption($modx, $namespace, $field, 'text', $setting);

        if ($required && ($value === null || $value === '')) {
            throw new RequiredSettingException("modai.global.text.$setting");
        }

        return $value;
    }

    /**
     * @throws RequiredSettingException
     */
    public static function getImageSetting(modX $modx, string $field, string $setting, string $namespace = 'modai', bool $required = true): ?string
    {
        $value = self::getOption($modx, $namespace, $field, 'image', $setting);

        if ($required && ($value === null || $value === '')) {
            throw new RequiredSettingException("modai.global.image.$setting");
        }

        return $value;
    }

    /**
     * @throws RequiredSettingException
     */
    public static function getVisionSetting(modX $modx, string $field, string $setting, string $namespace = 'modai', bool $required = true): ?string
    {
        $value = self::getOption($modx, $namespace, $field, 'vision', $setting);

        if ($required && ($value === null || $value === '')) {
            throw new RequiredSettingException("modai.global.vision.$setting");
        }

        return $value;
    }

    public static function getSetting(modX $modx, string $key, string $default = null): ?string
    {
        return $modx->getOption("modai.$key", null, $default);
    }

    public static function getApiSetting(modX $modx, string $service, string $key): ?string
    {
        return $modx->getOption("modai.api.$service.$key", null, $modx->getOption("modai.api.$key"));
    }
}
