<?php

namespace modAI\API\Prompt;

use modAI\API\API;
use modAI\Exceptions\APIException;
use modAI\Exceptions\LexiconException;
use modAI\Services\AIServiceFactory;
use modAI\Services\Config\ImageConfig;
use modAI\Settings;
use Psr\Http\Message\ServerRequestInterface;

class Image extends API
{
    use AdditionalOptions;

    public function post(ServerRequestInterface $request): void
    {
        if (!$this->modx->hasPermission('modai_client_chat_image')) {
            throw APIException::unauthorized();
        }

        set_time_limit(0);

        $data = $request->getParsedBody();

        $prompt = $this->modx->getOption('prompt', $data);
        $field = $this->modx->getOption('field', $data, '');
        $namespace = $this->modx->getOption('namespace', $data, 'modai');
        $attachments = $this->modx->getOption('attachments', $data, null);

        if (empty($prompt)) {
            throw new LexiconException('modai.error.prompt_required');
        }

        $additionalOptions = $this->getAdditionalOptions($data, $field, 'image');

        $model = Settings::getImageSetting($this->modx, $field, 'model', $namespace);
        $size = Settings::getImageSetting($this->modx, $field, 'size', $namespace, false) ?? '';
        $quality = Settings::getImageSetting($this->modx, $field, 'quality', $namespace, false) ?? '';
        $style = Settings::getImageSetting($this->modx, $field, 'style', $namespace, false) ?? '';
        $customOptions = Settings::getImageSetting($this->modx, $field, 'custom_options', $namespace, false);
        $responseFormat = Settings::getImageSetting($this->modx, $field, 'response_format', $namespace, false) ?? '';

        $aiService = AIServiceFactory::new($model, $this->modx);
        $result = $aiService->generateImage(
            $prompt,
            ImageConfig::new($model)
                ->options(['quality' => $quality, 'style' => $style, 'size' => $size, 'response_format' => $responseFormat], $customOptions, $additionalOptions)
                ->attachments($attachments)
        );

        $this->proxyAIResponse($result);
    }
}
