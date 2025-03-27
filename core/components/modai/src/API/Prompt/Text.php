<?php

namespace modAI\API\Prompt;

use modAI\API\API;
use modAI\Exceptions\LexiconException;
use modAI\Services\AIServiceFactory;
use modAI\Services\Config\CompletionsConfig;
use modAI\Settings;
use Psr\Http\Message\ServerRequestInterface;

class Text extends API
{
    private static array $validFields = ['res.pagetitle', 'res.longtitle', 'res.introtext', 'res.description'];

    public function post(ServerRequestInterface $request): void
    {
        set_time_limit(0);

        $data = $request->getParsedBody();

        $namespace = $this->modx->getOption('namespace', $data, 'modai');

        $fields = array_flip(self::$validFields);
        $field = $this->modx->getOption('field', $data, '');

        if (substr($field, 0, 3) === 'tv.') {
            $modAi = $this->modx->services->get('modai');
            $tvs = $modAi->getListOfTVs();
            $tvs = array_flip($tvs);

            $tvName = substr($field, 3);

            if (!isset($tvs[$tvName])) {
                throw new LexiconException('modai.error.unsupported_tv');
            }
        } else {
            if (!isset($fields[$field])) {
                throw new LexiconException('modai.error.unsupported_field');
            }
        }

        $resourceId = $this->modx->getOption('resourceId', $data);
        $content = $this->modx->getOption('content', $data);

        if (empty($resourceId) && empty($content)) {
            throw new LexiconException('modai.error.no_resource_specified');
        }

        if (!empty($resourceId)) {
            $resource = $this->modx->getObject('modResource', $resourceId);
            if (!$resource) {
                throw new LexiconException('modai.error.no_resource_found');
            }

            $content = $resource->getContent();

            if (empty($content)) {
                throw new LexiconException('modai.error.no_content');
            }
        }

        $systemInstructions = [];

        $stream = intval(Settings::getTextSetting($this->modx, $field, 'stream', $namespace)) === 1;
        $model = Settings::getTextSetting($this->modx, $field, 'model', $namespace);
        $temperature = (float)Settings::getTextSetting($this->modx, $field, 'temperature', $namespace);
        $maxTokens = (int)Settings::getTextSetting($this->modx, $field, 'max_tokens', $namespace);
        $output = Settings::getTextSetting($this->modx, $field, 'base_output', $namespace, false);
        $base = Settings::getTextSetting($this->modx, $field, 'base_prompt', $namespace, false);
        $fieldPrompt = Settings::getTextSetting($this->modx, $field, 'prompt', $namespace);
        $customOptions = Settings::getTextSetting($this->modx, $field, 'custom_options', $namespace, false);


        if (!empty($output)) {
            $systemInstructions[] = $output;
        }

        if (!empty($base)) {
            $systemInstructions[] = $base;
        }

        if (!empty($fieldPrompt)) {
            $systemInstructions[] = $fieldPrompt;
        }

        $aiService = AIServiceFactory::new($model, $this->modx);
        $result = $aiService->getCompletions(
            [$content],
            CompletionsConfig::new($model)
                ->customOptions($customOptions)
                ->maxTokens($maxTokens)
                ->temperature($temperature)
                ->systemInstructions($systemInstructions)
                ->stream($stream)
            //                ->toolChoice('none')
        );

        $this->proxyAIResponse($result);
    }
}
