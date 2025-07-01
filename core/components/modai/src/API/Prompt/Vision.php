<?php

namespace modAI\API\Prompt;

use modAI\API\API;
use modAI\Exceptions\APIException;
use modAI\Exceptions\LexiconException;
use modAI\Services\AIServiceFactory;
use modAI\Services\Config\VisionConfig;
use modAI\Settings;
use Psr\Http\Message\ServerRequestInterface;

class Vision extends API
{
    public function post(ServerRequestInterface $request): void
    {
        if (!$this->modx->hasPermission('modai_client_vision')) {
            throw APIException::unauthorized();
        }

        set_time_limit(0);

        $data = $request->getParsedBody();

        $field = $this->modx->getOption('field', $data);
        $namespace = $this->modx->getOption('namespace', $data, 'modai');
        $image = $this->modx->getOption('image', $data);
        $prompt = $this->modx->getOption('prompt', $data);

        if (empty($image)) {
            throw new LexiconException('modai.error.image_requried');
        }

        $stream = intval(Settings::getVisionSetting($this->modx, $field, 'stream', $namespace)) === 1;
        $model = Settings::getVisionSetting($this->modx, $field, 'model', $namespace);
        if (empty($prompt)) {
            $prompt = Settings::getVisionSetting($this->modx, $field, 'prompt', $namespace);
        }
        $customOptions = Settings::getVisionSetting($this->modx, $field, 'custom_options', $namespace, false);
        $maxTokens = (int)Settings::getVisionSetting($this->modx, $field, 'max_tokens', $namespace);

        $aiService = AIServiceFactory::new($model, $this->modx);
        $result = $aiService->getVision(
            $prompt,
            $image,
            VisionConfig::new($model, $this->modx)
                ->options(['max_tokens' => $maxTokens], $customOptions)
                ->stream($stream)
        );

        $this->proxyAIResponse($result);
    }
}
