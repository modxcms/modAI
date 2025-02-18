<?php
namespace modAI\Processors\Prompt;

use modAI\RequiredSettingException;
use modAI\Services\AIServiceFactory;
use modAI\Services\Config\CompletionsConfig;
use modAI\Settings;
use MODX\Revolution\Processors\Processor;

class FreeText extends Processor
{
    public function process()
    {
        $prompt = $this->getProperty('prompt');
        $field = $this->getProperty('field');

        if (empty($prompt)) {
            return $this->failure('Prompt is required.');
        }


        $systemInstructions = [];

        try {
            $model = Settings::getFieldSetting($this->modx, $field, 'model');
            $temperature = (float)Settings::getFieldSetting($this->modx, $field, 'temperature');
            $maxTokens = (int)Settings::getFieldSetting($this->modx, $field, 'max_tokens');
            $output = Settings::getFieldSetting($this->modx, $field, 'base.output', false);
        } catch (RequiredSettingException $e) {
            return $this->failure($e->getMessage());
        }

        if (!empty($output)) {
            $systemInstructions[] = $output;
        }

        $base = Settings::getPrompt($this->modx, 'global.base');
        if (!empty($base)) {
            $systemInstructions[] = $base;
        }

        $aiService = AIServiceFactory::new($model, $this->modx);

        try {
            $result = $aiService->getCompletions([$prompt], CompletionsConfig::new($model)->maxTokens($maxTokens)->temperature($temperature)->systemInstructions($systemInstructions));

            return $this->success('', ['content' => $result]);
        } catch (\Exception $e) {
            return $this->failure($e->getMessage());
        }
    }

}
