<?php

namespace modAI\Services;

use MODX\Revolution\modX;

class AIServiceFactory
{
    public static function new($model, modX &$modx): AIService
    {
        if (strncmp($model, 'gemini-', strlen('gemini-')) === 0) {
            return new Gemini($modx);
        }

        if (strncmp($model, 'imagen-', strlen('imagen-')) === 0) {
            return new Gemini($modx);
        }

        if (strncmp($model, 'claude-', strlen('claude-')) === 0) {
            return new Claude($modx);
        }

        if (strncmp($model, 'custom_', strlen('custom_')) === 0) {
            $compatibility = $modx->getOption('modai.api.custom.compatibility');
            if ($compatibility === 'openai') {
                return new CustomChatGPT($modx);
            }

            throw new \Error($modx->lexicon('modai.error.compatability_mode'));
        }

        switch ($model) {
            case 'text-embedding-004':
            case 'learnlm-1.5-pro-experimental':
                return new Gemini($modx);
            default:
                return new ChatGPT($modx);
        }
    }
}
