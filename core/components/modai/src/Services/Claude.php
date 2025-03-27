<?php
namespace modAI\Services;

use modAI\Exceptions\LexiconException;
use modAI\Services\Config\CompletionsConfig;
use modAI\Services\Config\ImageConfig;
use modAI\Services\Config\VisionConfig;
use modAI\Services\Response\AIResponse;
use modAI\Utils;
use MODX\Revolution\modX;

class Claude extends BaseService
{
    private modX $modx;

    const COMPLETIONS_API = 'https://api.anthropic.com/v1/messages';

    public function __construct(modX &$modx)
    {
        $this->modx =& $modx;
    }

    protected function formatImageMessage($img): array
    {
        $data = Utils::parseDataURL($img);

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

    protected function formatMessageContentItem($item): array
    {
        if ($item['type'] === 'text') {
            return [
                'type' => 'text',
                'text' => $item['value'],
            ];
        }

        if ($item['type'] === 'image') {
            return $this->formatImageMessage($item['value']);
        }

        throw new LexiconException("modai.error.unsupported_content_type", ['type' => $item['type']] );
    }

    public function getCompletions(array $data, CompletionsConfig $config): AIResponse
    {
        $apiKey = $this->modx->getOption('modai.api.claude.key');
        if (empty($apiKey)) {
            throw new LexiconException('modai.error.invalid_api_key', ['service' => 'claude']);
        }

        $messages = [];

        foreach ($config->getMessages() as $msg) {
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
                continue;
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
        $input["model"] = $config->getModel();
        $input["max_tokens"] = $config->getMaxTokens();
        $input["temperature"] = $config->getTemperature();
        $input["messages"] = $messages;

        if ($config->isStream()) {
            $input['stream'] = true;
        }

        $system = $config->getSystemInstructions();
        if (!empty($system)) {
            $input['system'] = $system;
        }

        $tools = [];
        foreach ($config->getTools() as $toolClass) {
            $params = $toolClass::getParameters();
            if (empty($params)) {
                $params = [
                    'type' => 'object',
                    'properties' => null,
                ];
            }

            $tools[] = [
                'name' => $toolClass::getName(),
                'description' => $toolClass::getDescription(),
                'input_schema' => (object)$params,
            ];
        }

        if (!empty($tools)) {
            $input['tools'] = $tools;
        }

        $input['tool_choice'] = [
            'type' => $config->getToolChoice()
        ];

        return AIResponse::new('claude')
            ->withStream($config->isStream())
            ->withParser('content')
            ->withUrl(self::COMPLETIONS_API)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01',
                'x-api-key' =>  $apiKey
            ])
            ->withBody($input);
    }

    public function getVision(string $prompt, string $image, VisionConfig $config): AIResponse
    {
        $apiKey = $this->modx->getOption('modai.api.claude.key');
        if (empty($apiKey)) {
            throw new LexiconException('modai.error.invalid_api_key', ['service' => 'claude']);
        }

        $input = $config->getCustomOptions();
        $input['model'] = $config->getModel();
        $input["max_tokens"] = $config->getMaxTokens();
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
        return AIResponse::new('claude')
            ->withStream($config->isStream())
            ->withParser('content')
            ->withUrl(self::COMPLETIONS_API)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01',
                'x-api-key' =>  $apiKey
            ])
            ->withBody($input);
    }

    public function generateImage(string $prompt, ImageConfig $config): AIResponse
    {
        throw new LexiconException('modai.error.not_implemented');
    }

}
