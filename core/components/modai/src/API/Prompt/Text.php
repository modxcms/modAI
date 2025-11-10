<?php

namespace modAI\API\Prompt;

use modAI\API\API;
use modAI\Exceptions\APIException;
use modAI\Exceptions\LexiconException;
use modAI\Services\AIServiceFactory;
use modAI\Services\Config\CompletionsConfig;
use modAI\Settings;
use modAI\Utils;
use Psr\Http\Message\ServerRequestInterface;
use MODX\Revolution\modResource;

class Text extends API
{
    private static array $validFields = ['res.pagetitle', 'res.longtitle', 'res.introtext', 'res.description'];

    public function post(ServerRequestInterface $request): void
    {
        $contextKey = null;
        
        if (!$this->modx->hasPermission('modai_client_text')) {
            throw APIException::unauthorized();
        }

        set_time_limit(0);

        $data = $request->getParsedBody();

        $namespace = Utils::getOption('namespace', $data, 'modai');

        $fields = array_flip(self::$validFields);
        $field = Utils::getOption('field', $data, '');

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

        $resourceId = Utils::getOption('resourceId', $data);
        $content = Utils::getOption('content', $data);

        if (empty($resourceId) && empty($content)) {
            throw new LexiconException('modai.error.no_resource_specified');
        }

        if (!empty($resourceId)) {
            /** @var modResource $resource */
            $resource = $this->modx->getObject('modResource', $resourceId);
            if (!$resource) {
                throw new LexiconException('modai.error.no_resource_found');
            }
            $contextKey = $resource->get('context_key');
            $content = $resource->getContent();

            if (empty($content)) {
                throw new LexiconException('modai.error.no_content');
            }
        }

        $systemInstructions = [];

        $stream = intval(Settings::getTextSetting($this->modx, $field, 'stream', $namespace)) === 1;
        $model = Settings::getTextSetting($this->modx, $field, 'model', $namespace, true, $contextKey);
        $temperature = (float)Settings::getTextSetting($this->modx, $field, 'temperature', $namespace , true, $contextKey);
        $maxTokens = (int)Settings::getTextSetting($this->modx, $field, 'max_tokens', $namespace, true, $contextKey);
        $output = Settings::getTextSetting($this->modx, $field, 'base_output', $namespace, false, $contextKey);
        $base = Settings::getTextSetting($this->modx, $field, 'base_prompt', $namespace, false, $contextKey);
        $fieldPrompt = Settings::getTextSetting($this->modx, $field, 'prompt', $namespace, true, $contextKey);
        $customOptions = Settings::getTextSetting($this->modx, $field, 'custom_options', $namespace, false, $contextKey);

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
            [['content' => $content]],
            CompletionsConfig::new($model, $this->modx)
                ->options(['max_tokens' => $maxTokens, 'temperature' => $temperature], $customOptions)
                ->systemInstructions($systemInstructions)
                ->stream($stream)
            //                ->toolChoice('none')
        );

        $this->proxyAIResponse($result);
    }
}
