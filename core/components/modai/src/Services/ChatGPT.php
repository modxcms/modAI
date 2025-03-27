<?php

namespace modAI\Services;

use modAI\Exceptions\LexiconException;
use modAI\Services\Config\CompletionsConfig;
use modAI\Services\Config\ImageConfig;
use modAI\Services\Config\VisionConfig;
use modAI\Services\Response\AIResponse;
use MODX\Revolution\modX;

class ChatGPT extends BaseService
{
    private modX $modx;

    const COMPLETIONS_API = 'https://api.openai.com/v1/chat/completions';
    const IMAGES_API = 'https://api.openai.com/v1/images/generations';

    public function __construct(modX &$modx)
    {
        $this->modx =& $modx;
    }

    protected function formatMessageContentItem($item): array
    {
        if ($item['type'] === 'text') {
            return [
                'type' => 'text',
                'text' => $item['value'],
            ];
        }

        if ($item['type'] === 'image') {
            return [
                'type' => 'image_url',
                'image_url' => [
                    'url' => $item['value']
                ],
            ];
        }

        throw new LexiconException("modai.error.unsupported_content_type", ['type' => $item['type']] );
    }

    public function getCompletions(array $data, CompletionsConfig $config): AIResponse
    {
        $apiKey = $this->modx->getOption('modai.api.chatgpt.key');
        if (empty($apiKey)) {
            throw new LexiconException('modai.error.invalid_api_key', ['service' => 'chatgpt']);
        }

        $messages = [];

        $system = $config->getSystemInstructions();
        if (!empty($system)) {
            $messages[] = [
                'role' => 'system',
                'content' => $system
            ];
        }

        foreach ($config->getMessages() as $msg) {
            if ($msg['role'] === 'tool') {
                foreach ($msg['content'] as $toolResponse) {
                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolResponse['id'],
                        'content' => $toolResponse['content'],
                    ];
                }
                continue;
            }

            if ($msg['role'] === 'assistant' && $msg['toolCalls']) {
                $toolCalls = [];

                foreach ($msg['toolCalls'] as $toolCall) {
                    $toolCalls[] = [
                        'id' => $toolCall['id'],
                        'type' => 'function',
                        'function' => [
                            "name" => $toolCall['name'],
                            "arguments" => $toolCall['arguments']
                        ]
                    ];
                }

                $messages[] = [
                    'role' => 'assistant',
                    'tool_calls' => $toolCalls
                ];

                continue;
            }

            $messages[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'assistant',
                'content' => $this->formatUserMessageContent($msg['content'])
            ];
        }

        foreach ($data as $msg) {
            $messages[] = [
                'role' => 'user',
                'content' => $this->formatUserMessageContent($msg)
            ];
        }

        $input = $config->getCustomOptions();
        $input['model'] = $config->getModel();
        $input['max_tokens'] = $config->getMaxTokens();
        $input['temperature'] = $config->getTemperature();
        $input['messages'] = $messages;

        $tools = [];
        foreach ($config->getTools() as $toolClass) {
            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name' => $toolClass::getName(),
                    'description' => $toolClass::getDescription(),
                    'parameters' => (object)$toolClass::getParameters(),
                ]
            ];
        }
        if (!empty($tools)) {
            $input['tools'] = $tools;

            $input['tool_choice'] = $config->getToolChoice();
        }

        if ($config->isStream()) {
            $input['stream'] = true;

            $input['stream_options'] = [
                'include_usage' => true,
            ];
        }

        return AIResponse::new('chatgpt')
            ->withStream($config->isStream())
            ->withParser('content')
            ->withUrl(self::COMPLETIONS_API)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey
            ])
            ->withBody($input);
    }

    public function getVision(string $prompt, string $image, VisionConfig $config): AIResponse
    {
        $apiKey = $this->modx->getOption('modai.api.chatgpt.key');
        if (empty($apiKey)) {
            throw new LexiconException('modai.error.invalid_api_key', ['service' => 'chatgpt']);
        }

        $input = $config->getCustomOptions();
        $input['model'] = $config->getModel();
        $input['max_tokens'] = $config->getMaxTokens();
        $input['messages'] = [
            [
                'role' => 'user',
                'content' => [
                    [
                        "type" => "text",
                        "text" => $prompt,
                    ],
                    [
                        "type" => "image_url",
                        "image_url" => ["url" => $image],
                    ],
                ]
            ]
        ];

        if ($config->isStream()) {
            $input['stream'] = true;
        }

        return AIResponse::new('chatgpt')
            ->withStream($config->isStream())
            ->withParser('content')
            ->withUrl(self::COMPLETIONS_API)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey
            ])
            ->withBody($input);
    }


    public function generateImage(string $prompt, ImageConfig $config): AIResponse
    {
        $apiKey = $this->modx->getOption('modai.api.chatgpt.key');
        if (empty($apiKey)) {
            throw new LexiconException('modai.error.invalid_api_key', ['service' => 'chatgpt']);
        }

        $input = $config->getCustomOptions();
        $input['prompt'] = $prompt;
        $input['model'] = $config->getModel();
        $input['n'] = $config->getN();
        $input['size'] = $config->getSize();
        $input['quality'] = $config->getQuality();
        $input['style'] = $config->getStyle();
        $input['response_format'] = 'url';

        return AIResponse::new('chatgpt')
            ->withParser('image')
            ->withUrl(self::IMAGES_API)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey
            ])
            ->withBody($input);
    }

}
