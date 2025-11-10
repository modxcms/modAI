<?php

namespace modAI;

use modAI\Exceptions\RequiredSettingException;
use MODX\Revolution\modX;

class Settings
{
    /**
     * Get model setting based on namespace.field.area
     *
     * The waterfall is as follows for namespace: custom_namespace, field: res.content, area: text and setting: model
     * {#sys}.{res.content}.{text}.{model}
     * {#sys}.{global}.{text}.{model}
     * {custom_namespace}.{res.content}.{text}.{model}
     * {custom_namespace}.{global}.{text}.{model}
     * {modai}.{res.content}.{text}.{model}
     * {modai}.{global}.{text}.{model}
     *
     * @param modX $modx
     * @param string $namespace
     * @param string $field
     * @param string $area
     * @param string $setting
     * @return string|null
     */
    private static function getOption(modX $modx, string $namespace, string $field, string $area, string $setting, ?string $contextKey = null): ?string
    {
        $handler = $modx;
        
        if (!empty($contextKey)) {
            $context = $modx->getContext($contextKey);
            if ($context) {
                $handler = $context;
            }
        }
        
        if (!empty($field)) {
            $value = $handler->getOption("#sys.$field.$area.$setting");
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        $value = $handler->getOption("#sys.global.$area.$setting");
        if ($value !== null && $value !== '') {
            return $value;
        }

        if (!empty($field)) {
            $value = $handler->getOption("$namespace.$field.$area.$setting");
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        $value = $handler->getOption("$namespace.global.$area.$setting");
        if ($value !== null && $value !== '') {
            return $value;
        }

        if ($namespace === 'modai') {
            return null;
        }

        if (!empty($field)) {
            $value = $handler->getOption("modai.$field.$area.$setting");
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        $value = $handler->getOption("modai.global.$area.$setting");
        if ($value !== null && $value !== '') {
            return $value;
        }

        return null;
    }

    /**
     * @throws RequiredSettingException
     */
    public static function getTextSetting(modX $modx, string $field, string $setting, string $namespace = 'modai', bool $required = true, ?string $contextKey = null): ?string
    {
        $value = self::getOption($modx, $namespace, $field, 'text', $setting, $contextKey);

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
    public static function getVisionSetting(modX $modx, string $field, string $setting, string $namespace = 'modai', bool $required = true, ?string $contextKey = null): ?string
    {
        $value = self::getOption($modx, $namespace, $field, 'vision', $setting, $contextKey);

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

    public static function setTextSetting(modX $modx, string $field, string $setting, string $value): void
    {
        if (empty($field)) {
            $field = 'global';
        }

        self::setSetting($modx, "$field.text.$setting", $value);
    }

    public static function setImageSetting(modX $modx, string $field, string $setting, string $value): void
    {
        if (empty($field)) {
            $field = 'global';
        }


        self::setSetting($modx, "$field.image.$setting", $value);
    }

    public static function setVisionSetting(modX $modx, string $field, string $setting, string $value): void
    {
        if (empty($field)) {
            $field = 'global';
        }


        self::setSetting($modx, "$field.vision.$setting", $value);
    }

    public static function setSetting(modX $modx, string $key, string $value): void
    {
        $modx->setOption("#sys.$key", $value);
    }
}
