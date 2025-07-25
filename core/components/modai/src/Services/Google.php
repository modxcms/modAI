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

class Google implements AIService
{
    use ApiKey;

    private modX $modx;

    const COMPLETIONS_API = 'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent?key={apiKey}';
    const COMPLETIONS_STREAM_API = 'https://generativelanguage.googleapis.com/v1beta/models/{model}:streamGenerateContent?key={apiKey}';
    const IMAGES_API = 'https://generativelanguage.googleapis.com/v1beta/models/{model}:predict?key={apiKey}';

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
                    'parts' => [
                        'text' => "User's selected text, user instructions should apply only on this text: " . $ctx['value']
                    ]
                ];
            }

            if ($ctx['__type'] === 'agent') {
                $messages[] = [
                    'role' => 'user',
                    'parts' => [
                        'text' => $ctx['value']
                    ]
                ];
            }

            if ($ctx['__type'] === 'ContextProvider') {
                $messages[] = [
                    'role' => 'user',
                    'parts' => [
                        'text' => $ctx['value']
                    ]
                ];
            }
        }
    }

    private function formatMessageAttachments(array &$messages, array $attachments, array &$currentMessage)
    {
        foreach ($attachments as $attachment) {
            if ($attachment['__type'] === 'image') {
                $data = Utils::parseDataURL($attachment['value']);
                if (is_string($data)) {
                    $currentMessage['parts'][] = [
                        'file_data' => [
                            "mime_type" => "image/jpeg",
                            "file_uri" => $data,
                        ]
                    ];
                    continue;
                }

                $currentMessage['parts'][] = [
                    'inline_data' => [
                        "mime_type" => $data['mimeType'],
                        "data" => $data['base64'],
                    ]
                ];
            }
        }
    }

    private function addMessage(array &$messages, array $msg): void
    {
        if ($msg['role'] === 'tool') {
            $content = [];

            foreach ($msg['content'] as $toolResponse) {
                $content[] = [
                    'functionResponse' => [
                        'name' => $toolResponse['name'],
                        'response' => [
                            'name' => $toolResponse['name'],
                            'content' => json_decode($toolResponse['content'], true),
                        ]
                    ]
                ];
            }

            $messages[] = [
                'role' => 'user',
                'parts' => $content
            ];

            return;
        }

        if ($msg['role'] === 'assistant' && $msg['toolCalls']) {
            $content = [];

            foreach ($msg['toolCalls'] as $toolCall) {
                $content[] = [
                    'functionCall' => [
                        "name" => $toolCall['name'],
                        "args" => (object)json_decode($toolCall['arguments'], true)
                    ]
                ];
            }

            $messages[] = [
                'role' => 'model',
                'parts' => $content
            ];

            return;
        }

        if ($msg['role'] === 'user') {
            $currentMessage = [
                'role' => 'user',
                'parts' => [
                    [
                        'text' => 'User instructions: ' . $msg['content']
                    ]
                ]
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
            'role' => 'model',
            'parts' => [
                [
                    'text' => $msg['content']
                ]
            ]
        ];
    }

    public function getCompletions(array $data, CompletionsConfig $config): AIResponse
    {
        $apiKey = $this->getApiKey();

        $url = self::COMPLETIONS_API;

        if ($config->isStream()) {
            $url = self::COMPLETIONS_STREAM_API;
        }

        $url = str_replace("{model}", $config->getModel(), $url);
        $url = str_replace("{apiKey}", $apiKey, $url);

        $systemInstruction = [];

        $system = $config->getSystemInstructions();
        if (!empty($system)) {
            $systemInstruction[] = [
                'text' => $system
            ];
        }

        $messages = [];

        foreach ($config->getMessages() as $msg) {
            $this->addMessage($messages, $msg);
        }

        foreach ($data as $msg) {
            $this->addMessage($messages, $msg);
        }

        $input = $config->getOptions(function ($options) {
            $cfg = [];
            $generationConfig = [];

            foreach ($options as $key => $value) {
                switch ($key) {
                    case 'max_tokens':
                        $generationConfig['maxOutputTokens'] = $value;
                        break;
                    case 'temperature':
                        $value = floatval($value);
                        if ($value >= 0) {
                            $generationConfig['temperature'] = $value;
                        }
                        break;
                    default:
                        $cfg[$key] = $value;
                }
            }

            if (!empty($generationConfig)) {
                $cfg['generationConfig'] = $generationConfig;
            }

            return $cfg;
        });
        $input["contents"] = $messages;

        if (!empty($systemInstruction)) {
            $input['system_instruction'] = [
                "parts" => $systemInstruction
            ];
        }

        $tools = [];
        foreach ($config->getTools() as $tool) {
            $params = $tool['parameters'];
            if (empty($params)) {
                $params = null;
            }

            $tools[] = [
                'name' => $tool['name'],
                'description' => $tool['description'],
                'parameters' => $params,
            ];
        }

        if (!empty($tools)) {
            $input['tools'] = [
                'function_declarations' => $tools
            ];
        }

        $input['tool_config'] = [
            'function_calling_config' => [
                'mode' => $config->getToolChoice()
            ]
        ];

        return AIResponse::new(self::getServiceName(), $config->getRawModel())
            ->withStream($config->isStream())
            ->withParser('content')
            ->withUrl($url)
            ->withBody($input);
    }

    public function getVision(string $prompt, string $image, VisionConfig $config): AIResponse
    {
        $apiKey = $this->getApiKey();

        $image = str_replace('data:image/png;base64,', '', $image);

        $input = $config->getOptions(function ($options) {
            $cfg = [];
            $generationConfig = [];

            foreach ($options as $key => $value) {
                switch ($key) {
                    case 'max_tokens':
                        $generationConfig['maxOutputTokens'] = $value;
                        break;
                    default:
                        $cfg[$key] = $value;
                }
            }

            if (!empty($generationConfig)) {
                $cfg['generationConfig'] = $generationConfig;
            }

            return $cfg;
        });

        $input['contents'] = [
            'parts' => [
                [
                    "text" => $prompt,
                ],
                [
                    "inline_data" => [
                        "mime_type" => "image/png",
                        "data" => $image,
                    ]
                ],
            ]
        ];

        $url = self::COMPLETIONS_API;

        if ($config->isStream()) {
            $url = self::COMPLETIONS_STREAM_API;
        }

        $url = str_replace("{model}", $config->getModel(), $url);
        $url = str_replace("{apiKey}", $apiKey, $url);

        return AIResponse::new(self::getServiceName(), $config->getRawModel())
            ->withStream($config->isStream())
            ->withParser('content')
            ->withUrl($url)
            ->withBody($input);
    }

    public function generateImage(string $prompt, ImageConfig $config): AIResponse
    {
        $apiKey = $this->getApiKey();

        $url = self::IMAGES_API;
        $url = str_replace("{model}", $config->getModel(), $url);
        $url = str_replace("{apiKey}", $apiKey, $url);

        $input = $config->getOptions(function ($options) {
            $cfg = [
                'parameters' => [
                    'sampleCount' => 1
                ]
            ];

            return $cfg;
        });

        $input["instances"] = [
            "prompt" => $prompt,
        ];

        return AIResponse::new(self::getServiceName(), $config->getRawModel())
            ->withParser('image')
            ->withUrl($url)
            ->withBody($input);
    }

    public static function getServiceName(): string
    {
        return 'google';
    }

    public static function isMyModel(string $model): bool
    {
        $prefix = 'google/';

        return strncmp($model, $prefix, strlen($prefix)) === 0;
    }
}
