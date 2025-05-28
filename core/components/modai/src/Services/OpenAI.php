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

class OpenAI implements AIService
{
    use ApiKey;

    private modX $modx;

    const COMPLETIONS_API = 'https://api.openai.com/v1/chat/completions';
    const IMAGES_EDIT_API = 'https://api.openai.com/v1/images/edits';
    const IMAGES_API = 'https://api.openai.com/v1/images/generations';

    public function __construct(modX &$modx)
    {
        $this->modx =& $modx;
    }

    private function formatMessageContexts(array &$messages, array $contexts, array &$currentMessage)
    {
        foreach ($contexts as $ctx) {
            if ($ctx['__type'] === 'selection') {
                $messages[] = [
                    'role' => 'developer',
                    'content' => "Next user message should act only on this text: " . $ctx['value']
                ];
            }

            if ($ctx['__type'] === 'agent') {
                $messages[] = [
                    'role' => 'developer',
                    'content' => $ctx['value']
                ];
            }

            if ($ctx['__type'] === 'ContextProvider') {
                $messages[] = [
                    'role' => 'developer',
                    'content' => $ctx['value']
                ];
            }
        }
    }

    private function formatMessageAttachments(array &$messages, array $attachments, array &$currentMessage)
    {
        foreach ($attachments as $attachment) {
            if ($attachment['__type'] === 'image') {
                $content = is_string($currentMessage['content']) ? [['type' => 'text', 'text' => $currentMessage['content']]] : $currentMessage['content'];

                $content[] = [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => $attachment['value']
                    ]
                ];

                $currentMessage['content'] = $content;
            }
        }
    }

    private function addMessage(array &$messages, array $msg): void
    {
        if ($msg['role'] === 'tool') {
            foreach ($msg['content'] as $toolResponse) {
                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolResponse['id'],
                    'content' => $toolResponse['content'],
                ];
            }
            return;
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

            return;
        }

        if ($msg['role'] === 'user') {
            $currentMessage = [
                'role' => 'user',
                'content' => $msg['content']
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

        $system = $config->getSystemInstructions();
        if (!empty($system)) {
            $messages[] = [
                'role' => 'developer',
                'content' => $system
            ];
        }

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
                    case 'max_tokens':
                        $gptConfig['max_completion_tokens'] = $value;
                        break;
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

        $input['model'] = $config->getModel();
        $input['messages'] = $messages;

        $tools = [];
        foreach ($config->getTools() as $tool) {
            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name' => $tool['name'],
                    'description' => $tool['description'],
                    'parameters' => (object)$tool['parameters'],
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

        return AIResponse::new(self::getServiceName(), $config->getRawModel())
            ->withStream($config->isStream())
            ->withParser('content')
            ->withUrl(self::COMPLETIONS_API)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey
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
                    [
                        "type" => "image_url",
                        "image_url" => ["url" => $image],
                    ],
                ]
            ]
        ];

        if ($config->isStream()) {
            $input['stream'] = true;

            $input['stream_options'] = [
                'include_usage' => true,
            ];
        }

        return AIResponse::new(self::getServiceName(), $config->getRawModel())
            ->withStream($config->isStream())
            ->withParser('content')
            ->withUrl(self::COMPLETIONS_API)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey
            ])
            ->withBody($input);
    }


    public function generateImage(string $prompt, ImageConfig $config): AIResponse
    {
        $attachments = $config->getAttachments();
        if (!empty($attachments)) {
            return $this->editImage($prompt, $config);
        }

        $apiKey = $this->getApiKey();

        $input = $config->getOptions(function ($options) {
            $gptConfig = [
                'n' => 1,
            ];

            foreach ($options as $key => $value) {
                if (empty($value)) {
                    continue;
                }

                $gptConfig[$key] = $value;
            }

            return $gptConfig;
        });

        $input['prompt'] = $prompt;
        $input['model'] = $config->getModel();

        return AIResponse::new(self::getServiceName(), $config->getRawModel())
            ->withParser('image')
            ->withUrl(self::IMAGES_API)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey
            ])
            ->withBody($input);
    }

    public function editImage(string $prompt, ImageConfig $config): AIResponse
    {
        $apiKey = $this->getApiKey();

        $input = $config->getOptions(function ($options) {
            $gptConfig = [
                'n' => 1,
            ];

            foreach ($options as $key => $value) {
                if (empty($value)) {
                    continue;
                }

                $gptConfig[$key] = $value;
            }

            return $gptConfig;
        });

        $input['prompt'] = $prompt;
        $input['model'] = $config->getModel();

        $binary = [];
        $attachments = $config->getAttachments();
        foreach ($attachments as $attachment) {
            if ($attachment['__type'] !== 'image') {
                continue;
            }

            $data = Utils::parseDataURL($attachment['value']);
            if (is_string($data)) {
                $imageData = file_get_contents($data);
                if ($imageData === false) {
                    throw new LexiconException("modai.error.failed_to_fetch_image");
                }
                $info = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $info->buffer($imageData);
                $base64 = base64_encode($imageData);

                $data = [
                    'base64' => $base64,
                    'mimeType' => $mimeType
                ];
            }

            if (!isset($binary['image'])) {
                $binary['image'] = [];
            }

            $binary['image'][] = $data;
        }

        return AIResponse::new(self::getServiceName(), $config->getRawModel(), "multipart/form-data")
            ->withParser('image')
            ->withUrl(self::IMAGES_EDIT_API)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey
            ])
            ->withBody($input)
            ->withBinary($binary);
    }

    public static function getServiceName(): string
    {
        return 'openai';
    }

    public static function isMyModel(string $model): bool
    {
        $prefix = 'openai/';

        return strncmp($model, $prefix, strlen($prefix)) === 0;
    }
}
