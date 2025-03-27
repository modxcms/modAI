<?php
namespace modAI\Services;

use modAI\Exceptions\LexiconException;
use modAI\Services\Config\CompletionsConfig;
use modAI\Services\Config\ImageConfig;
use modAI\Services\Config\VisionConfig;
use modAI\Services\Response\AIResponse;
use modAI\Utils;
use MODX\Revolution\modX;

class Gemini extends BaseService {
    private modX $modx;

    const COMPLETIONS_API = 'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent?key={apiKey}';
    const COMPLETIONS_STREAM_API = 'https://generativelanguage.googleapis.com/v1beta/models/{model}:streamGenerateContent?key={apiKey}';
    const IMAGES_API = 'https://generativelanguage.googleapis.com/v1beta/models/{model}:predict?key={apiKey}';

    public function __construct(modX &$modx)
    {
        $this->modx =& $modx;
    }

    protected function formatMessageContentItem($item): array
    {
        if ($item['type'] === 'text') {
            return [
                'text' => $item['value']
            ];
        }

        if ($item['type'] === 'image') {
            $data = Utils::parseDataURL($item['value']);
            if (is_string($data)) {
                return [
                    'file_data' => [
                        "mime_type" => "image/jpeg",
                        "file_uri" => $data,
                    ]
                ];
            }

            return [
                'inline_data' => [
                    "mime_type" => $data['mimeType'],
                    "data" => $data['base64'],
                ]
            ];
        }

        throw new LexiconException("modai.error.unsupported_content_type", ['type' => $item['type']] );
    }

    protected function formatStringMessageContent(string $item)
    {
        return [[
            'text' => $item
        ]];
    }

    public function getCompletions(array $data, CompletionsConfig $config): AIResponse
    {
        $apiKey = $this->modx->getOption('modai.api.gemini.key');
        if (empty($apiKey)) {
            throw new LexiconException('modai.error.invalid_api_key', ['service' => 'gemini']);
        }

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
                continue;
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

                continue;
            }

            $messages[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'model',
                'parts' => $this->formatUserMessageContent($msg['content'])
            ];
        }

        foreach ($data as $msg) {
            $messages[] = [
                'role' => 'user',
                'parts' => $this->formatUserMessageContent($msg)
            ];
        }

        $input = $config->getCustomOptions();
        $input["contents"] = $messages;

        $input["generationConfig"] = [
            "temperature" => $config->getTemperature(),
            "maxOutputTokens" => $config->getMaxTokens(),
        ];

        if (!empty($systemInstruction)) {
            $input['system_instruction'] = [
                "parts" => $systemInstruction
            ];
        }

        $tools = [];
        foreach ($config->getTools() as $toolClass) {
            $params = $toolClass::getParameters();
            if (empty($params)) {
                $params = null;
            }

            $tools[] = [
                'name' => $toolClass::getName(),
                'description' => $toolClass::getDescription(),
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

        return AIResponse::new('gemini')
            ->withStream($config->isStream())
            ->withParser('content')
            ->withUrl($url)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->withBody($input);
    }

    public function getVision(string $prompt, string $image, VisionConfig $config): AIResponse
    {
        $apiKey = $this->modx->getOption('modai.api.gemini.key');
        if (empty($apiKey)) {
            throw new LexiconException('modai.error.invalid_api_key', ['service' => 'gemini']);
        }

        $image = str_replace('data:image/png;base64,', '', $image);

        $input = $config->getCustomOptions();
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

        $input["generationConfig"] = [
            "maxOutputTokens" => $config->getMaxTokens(),
        ];

        $url = self::COMPLETIONS_API;

        if ($config->isStream()) {
            $url = self::COMPLETIONS_STREAM_API;
        }

        $url = str_replace("{model}", $config->getModel(), $url);
        $url = str_replace("{apiKey}", $apiKey, $url);

        return AIResponse::new('gemini')
            ->withStream($config->isStream())
            ->withParser('content')
            ->withUrl($url)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->withBody($input);
    }

    public function generateImage(string $prompt, ImageConfig $config): AIResponse
    {
        $apiKey = $this->modx->getOption('modai.api.gemini.key');
        if (empty($apiKey)) {
            throw new LexiconException('modai.error.invalid_api_key', ['service' => 'gemini']);
        }

        $url = self::IMAGES_API;
        $url = str_replace("{model}", $config->getModel(), $url);
        $url = str_replace("{apiKey}", $apiKey, $url);

        $input = $config->getCustomOptions();
        $input["instances"] = [
            "prompt" => $prompt,
        ];
        $input["parameters"] = [
            "sampleCount" => $config->getN()
        ];

        return AIResponse::new('gemini')
            ->withParser('image')
            ->withUrl($url)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->withBody($input);
    }
}
