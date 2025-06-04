<?php

namespace modAI\Services;

use modAI\Exceptions\LexiconException;
use modAI\Services\Config\CompletionsConfig;
use modAI\Services\Config\ImageConfig;
use modAI\Services\Config\VisionConfig;
use modAI\Services\Response\AIResponse;
use modAI\Tools\ToolInterface;
use modAI\Utils;
use MODX\Revolution\modX;

class Anthropic implements AIService
{
    use ApiKey;

    private modX $modx;

    const COMPLETIONS_API = 'https://api.anthropic.com/v1/messages';

    public function __construct(modX &$modx)
    {
        $this->modx =& $modx;
    }

    private function formatMessageContexts(array &$messages, array $contexts, array &$currentMessage)
    {
        foreach ($contexts as $ctx) {
            if ($ctx['__type'] === 'selection') {
                $messages[] = [
                    'role' => 'user',
                    'content' => "User's selected text, user instructions should apply only on this text: " . $ctx['value']
                ];
            }

            if ($ctx['__type'] === 'agent') {
                $messages[] = [
                    'role' => 'user',
                    'content' => $ctx['value']
                ];
            }

            if ($ctx['__type'] === 'ContextProvider') {
                $messages[] = [
                    'role' => 'user',
                    'content' => $ctx['value']
                ];
            }
        }
    }

    private function formatImageMessage(string $image)
    {
        $data = Utils::parseDataURL($image);
        if (is_string($data)) {
            $imageData = file_get_contents($data);
            if ($imageData === false) {
                throw new LexiconException("modai.error.failed_to_fetch_image");
            }
            $info = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $info->buffer($imageData);
            $base64 = base64_encode($imageData);

            return [
                'type' => 'image',
                'source' => [
                    'type' => 'base64',
                    'media_type' => $mimeType,
                    'data' => $base64,
                ],
            ];
        }

        return [
            'type' => 'image',
            'source' => [
                'type' => 'base64',
                'media_type' => $data['mimeType'],
                'data' => $data['base64'],
            ],
        ];
    }

    private function formatMessageAttachments(array &$messages, array $attachments, array &$currentMessage)
    {
        foreach ($attachments as $attachment) {
            if ($attachment['__type'] === 'image') {
                $content = is_string($currentMessage['content']) ? [['type' => 'text', 'text' => $currentMessage['content']]] : $currentMessage['content'];

                $content[] = $this->formatImageMessage($attachment['value']);

                $currentMessage['content'] = $content;
            }
        }
    }

    private function addMessage(array &$messages, array $msg): void
    {
        if ($msg['role'] === 'tool') {
            $content = [];
            foreach ($msg['content'] as $toolResponse) {
                $content[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $toolResponse['id'],
                    'content' => $toolResponse['content'],
                ];
            }
            $messages[] = [
                'role' => 'user',
                'content' => $content
            ];

            return;
        }

        if ($msg['role'] === 'assistant' && $msg['toolCalls']) {
            $content = [];

            foreach ($msg['toolCalls'] as $toolCall) {
                $content[] = [
                    'id' => $toolCall['id'],
                    'type' => 'tool_use',
                    "name" => $toolCall['name'],
                    "input" => (object)json_decode($toolCall['arguments'], true)
                ];
            }

            $messages[] = [
                'role' => 'assistant',
                'content' => $content
            ];

            return;
        }

        if ($msg['role'] === 'user') {
            $currentMessage = [
                'role' => 'user',
                'content' => 'User instructions: ' . $msg['content']
            ];

            if (isset($msg['contexts']) && is_array($msg['contexts'])) {
                $this->formatMessageContexts($messages, $msg['contexts'], $currentMessage);
            }

            if (isset($msg['attachments']) && is_array($msg['attachments'])) {
                $this->formatMessageAttachments($messages, $msg['attachments'], $currentMessage);
            }

            $messages[] = $currentMessage;

            return;
        }

        $messages[] = [
            'role' => 'assistant',
            'content' => $msg['content']
        ];
    }

    public function getCompletions(array $data, CompletionsConfig $config): AIResponse
    {
        $apiKey = $this->getApiKey();

        $messages = [];

        foreach ($config->getMessages() as $msg) {
            $this->addMessage($messages, $msg);
        }

        foreach ($data as $msg) {
            $this->addMessage($messages, $msg);
        }

        $input = $config->getOptions(function ($options) {
            $gptConfig = [];

            foreach ($options as $key => $value) {
                switch ($key) {
                    case 'temperature':
                        $value = floatval($value);
                        if ($value >= 0) {
                            $gptConfig['temperature'] = $value;
                        }
                        break;
                    default:
                        $gptConfig[$key] = $value;
                }
            }

            return $gptConfig;
        });

        $input["model"] = $config->getModel();
        $input["messages"] = $messages;

        if ($config->isStream()) {
            $input['stream'] = true;
        }

        $system = $config->getSystemInstructions();
        if (!empty($system)) {
            $input['system'] = $system;
        }

        $tools = [];
        foreach ($config->getTools() as $tool) {
            $params = $tool['parameters'];

            if (empty($params)) {
                $params = [
                    'type' => 'object',
                    'properties' => null,
                ];
            }

            $tools[] = [
                'name' => $tool['name'],
                'description' => $tool['description'],
                'input_schema' => (object)$params,
            ];
        }

        if (!empty($tools)) {
            $input['tools'] = $tools;

            $input['tool_choice'] = [
                'type' => $config->getToolChoice()
            ];
        }

        return AIResponse::new(self::getServiceName(), $config->getRawModel())
            ->withStream($config->isStream())
            ->withParser('content')
            ->withUrl(self::COMPLETIONS_API)
            ->withHeaders([
                'anthropic-version' => '2023-06-01',
                'x-api-key' =>  $apiKey
            ])
            ->withBody($input);
    }

    public function getVision(string $prompt, string $image, VisionConfig $config): AIResponse
    {
        $apiKey = $this->getApiKey();

        $input = $config->getOptions();
        $input['model'] = $config->getModel();
        $input['messages'] = [
            [
                'role' => 'user',
                'content' => [
                    [
                        "type" => "text",
                        "text" => $prompt,
                    ],
                    $this->formatImageMessage($image),
                ]
            ]
        ];

        if ($config->isStream()) {
            $input['stream'] = true;
        }
        return AIResponse::new(self::getServiceName(), $config->getRawModel())
            ->withStream($config->isStream())
            ->withParser('content')
            ->withUrl(self::COMPLETIONS_API)
            ->withHeaders([
                'anthropic-version' => '2023-06-01',
                'x-api-key' =>  $apiKey
            ])
            ->withBody($input);
    }

    public function generateImage(string $prompt, ImageConfig $config): AIResponse
    {
        throw new LexiconException('modai.error.not_implemented');
    }

    public static function getServiceName(): string
    {
        return 'anthropic';
    }

    public static function isMyModel(string $model): bool
    {
        $prefix = 'anthropic/';

        return strncmp($model, $prefix, strlen($prefix)) === 0;
    }
}
