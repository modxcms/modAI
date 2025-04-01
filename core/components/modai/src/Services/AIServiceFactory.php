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
        /** @var array<class-string<AIService>> $services */
        $services = [
            OpenAI::class,
            Google::class,
            Anthropic::class,
            CustomOpenAI::class
        ];

        foreach ($services as $service) {
            if ($service::isMyModel($model)) {
                return new $service($modx);
            }
        }

        throw new LexiconException('modai.error.invalid_model_name', ['model' => $model]);
    }
}
