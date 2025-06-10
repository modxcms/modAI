<?php

namespace modAI\Services;

use modAI\Exceptions\LexiconException;
use MODX\Revolution\modX;

class AIServiceFactory
{
    /**
     * @param $model
     * @param modX $modx
     * @return AIService
     * @throws LexiconException
     */
    public static function new($model, modX &$modx): AIService
    {
        /** @var array<class-string<AIService>> $allServices */
        $allServices = [];

        $registeredServices = $modx->invokeEvent('modAIOnServiceRegister');
        foreach ($registeredServices as $registeredService) {
            $services = $registeredService;

            if (!is_array($services)) {
                $maybeJSON = json_decode($registeredService, true);
                if (is_array($maybeJSON)) {
                    $services = $maybeJSON;
                } else {
                    $services = [$registeredService];
                }
            }

            if (!is_array($services)) {
                continue;
            }

            foreach ($services as $tool) {
                if (self::validateClassName($tool)) {
                    $allServices[] = $tool;
                }
            }
        }

        foreach ($allServices as $service) {
            if ($service::isMyModel($model)) {
                return new $service($modx);
            }
        }

        throw new LexiconException('modai.error.invalid_model_name', ['model' => $model]);
    }

    private static function validateClassName($class): bool
    {
        if (!class_implements($class, AIService::class)) {
            return false;
        }

        return true;
    }
}
