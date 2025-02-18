<?php
namespace modAI\Services;

use modAI\Services\Config\CompletionsConfig;
use modAI\Services\Config\ImageConfig;
use modAI\Services\Config\VisionConfig;
use MODX\Revolution\modX;

class CustomChatGPT implements AIService
{
    private modX $modx;

    const COMPLETIONS_API = '{url}/chat/completions';
    const IMAGES_API = '{url}/images/generations';

    public function __construct(modX &$modx)
    {
        $this->modx =& $modx;
    }

    public function generateImage(string $prompt, ImageConfig $config): string {
        $apiKey = $this->modx->getOption('modai.api.custom.key');
        if (empty($apiKey)) {
            throw new \Exception('Missing modai.api.custom.key');
        }

        $baseUrl = $this->modx->getOption('modai.api.custom.url');
        if (empty($baseUrl)) {
            throw new \Exception('Missing modai.api.custom.url');
        }

        $input = [
            'prompt' => $prompt,
            'model' => $config->getModel(),
            'n' => $config->getN(),
            'size' => $config->getSize(),
            'quality' => $config->getQuality()
        ];

        $url = self::IMAGES_API;
        $url = str_replace('{url}', $baseUrl, $url);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new \Exception($error_msg);
        }

        curl_close($ch);

        $result = json_decode($response, true);
        if (!is_array($result)) {
            throw new \Exception('Invalid response');
        }

        if (isset($result['error'])) {
            throw new \Exception($result['error']['message']);
        }

        if (!isset($result['data'][0]['url'])) {
            throw new \Exception("There was an error generating a response.");
        }

        return $result['data'][0]['url'];
    }

    /**
     * @throws \Exception
     */
    public function getCompletions(array $data, CompletionsConfig $config): string
    {
        $apiKey = $this->modx->getOption('modai.api.custom.key');
        if (empty($apiKey)) {
            throw new \Exception('Missing modai.api.custom.key');
        }

        $baseUrl = $this->modx->getOption('modai.api.custom.url');
        if (empty($baseUrl)) {
            throw new \Exception('Missing modai.api.custom.url');
        }

        $messages = [];

        foreach ($config->getSystemInstructions() as $system) {
            $messages[] = [
                'role' => 'system',
                'content' => $system
            ];
        }

        foreach ($data as $msg) {
            $messages[] = [
                'role' => 'user',
                'content' => $msg
            ];
        }

        $input = [
            'model' => $config->getModel(),
            'max_tokens' => $config->getMaxTokens(),
            'temperature' => $config->getTemperature(),
            'messages' => $messages,
        ];

        $url = self::COMPLETIONS_API;
        $url = str_replace('{url}', $baseUrl, $url);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new \Exception($error_msg);
        }

        curl_close($ch);

        $result = json_decode($response, true);
        if (!is_array($result)) {
            throw new \Exception('Invalid response');
        }

        if (isset($result['error'])) {
            throw new \Exception($result['error']['message']);
        }

        if (!isset($result['choices'][0]['message']['content'])) {
            throw new \Exception("There was an error generating a response: " . json_encode($result));
        }

        return $result['choices'][0]['message']['content'];
    }

    /**
     * @throws \Exception
     */
    public function getVision(string $prompt, string $image, VisionConfig $config): string
    {
        $apiKey = $this->modx->getOption('modai.api.custom.key');
        if (empty($apiKey)) {
            throw new \Exception('Missing modai.api.custom.key');
        }

        $baseUrl = $this->modx->getOption('modai.api.custom.url');
        if (empty($baseUrl)) {
            throw new \Exception('Missing modai.api.custom.url');
        }

        $input = [
            'model' => $config->getModel(),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            "type"=> "text",
                            "text"=> $prompt,
                        ],
                        [
                            "type" => "image_url",
                            "image_url" => ["url" => $image],
                        ],
                    ]
                ]
            ],
        ];

        $url = self::COMPLETIONS_API;
        $url = str_replace('{url}', $baseUrl, $url);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new \Exception($error_msg);
        }

        curl_close($ch);

        $result = json_decode($response, true);
        if (!is_array($result)) {
            throw new \Exception('Invalid response');
        }

        if (isset($result['error'])) {
            throw new \Exception($result['error']['message']);
        }

        if (!isset($result['choices'][0]['message']['content'])) {
            throw new \Exception("There was an error generating a response.");
        }

        return $result['choices'][0]['message']['content'];
    }

}
