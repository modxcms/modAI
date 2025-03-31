<?php

namespace modAI\API\Prompt;

use modAI\API\API;
use modAI\Exceptions\LexiconException;
use modAI\Model\Tool;
use modAI\Services\AIServiceFactory;
use modAI\Services\Config\CompletionsConfig;
use modAI\Settings;
use modAI\Tools\GetWeather;
use Psr\Http\Message\ServerRequestInterface;

class Chat extends API
{
    public function post(ServerRequestInterface $request): void
    {
        set_time_limit(0);

        $data = $request->getParsedBody();

        $prompt = $this->modx->getOption('prompt', $data);
        $field = $this->modx->getOption('field', $data, '');
        $contexts = $this->modx->getOption('contexts', $data, null);
        $attachments = $this->modx->getOption('attachments', $data, null);
        $namespace = $this->modx->getOption('namespace', $data, 'modai');
        $messages = $this->modx->getOption('messages', $data);

        if (empty($prompt) && empty($messages)) {
            throw new LexiconException('modai.error.prompt_required');
        }

        $systemInstructions = [];

        $stream = intval(Settings::getTextSetting($this->modx, $field, 'stream', $namespace)) === 1;
        $model = Settings::getTextSetting($this->modx, $field, 'model', $namespace);
        $temperature = (float)Settings::getTextSetting($this->modx, $field, 'temperature', $namespace);
        $maxTokens = (int)Settings::getTextSetting($this->modx, $field, 'max_tokens', $namespace);
        $output = Settings::getTextSetting($this->modx, $field, 'base_output', $namespace, false);
        $base = Settings::getTextSetting($this->modx, $field, 'base_prompt', $namespace, false);
        $customOptions = Settings::getTextSetting($this->modx, $field, 'custom_options', $namespace, false);

        if (!empty($output)) {
            $systemInstructions[] = $output;
        }

        if (!empty($base)) {
            $systemInstructions[] = $base;
        }

        $userMessages = [];

        if (!empty($prompt)) {
            $msg = ['content' => $prompt, 'role' => 'user'];

            if (!empty($contexts)) {
                $msg['contexts'] = $contexts;
            }

            if (!empty($attachments)) {
                $msg['attachments'] = $attachments;
            }

            $userMessages[] = $msg;
        }

        $tools = Tool::getAvailableTools($this->modx);

        $aiService = AIServiceFactory::new($model, $this->modx);
        $result = $aiService->getCompletions(
            $userMessages,
            CompletionsConfig::new($model)
                ->tools($tools)
                ->messages($messages)
                ->customOptions($customOptions)
                ->maxTokens($maxTokens)
                ->temperature($temperature)
                ->systemInstructions($systemInstructions)
                ->stream($stream)
        );

        $this->proxyAIResponse($result);
    }
}
