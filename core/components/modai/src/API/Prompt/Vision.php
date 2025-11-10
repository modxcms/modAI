<?php

namespace modAI\API\Prompt;

use modAI\API\API;
use modAI\Exceptions\APIException;
use modAI\Exceptions\LexiconException;
use modAI\Services\AIServiceFactory;
use modAI\Services\Config\VisionConfig;
use modAI\Settings;
use modAI\Utils;
use Psr\Http\Message\ServerRequestInterface;
use MODX\Revolution\modResource;

class Vision extends API
{
    public function post(ServerRequestInterface $request): void
    {
        $contextKey = null;
        if (!$this->modx->hasPermission('modai_client_vision')) {
            throw APIException::unauthorized();
        }

        set_time_limit(0);
        $data = $request->getParsedBody();

        $field = Utils::getOption('field', $data);
        $namespace = Utils::getOption('namespace', $data, 'modai');
        $image = Utils::getOption('image', $data);
        $prompt = Utils::getOption('prompt', $data);
        $resourceId = Utils::getOption('resourceId', $data);

        if (empty($image)) {
            throw new LexiconException('modai.error.image_requried');
        }

        if (!empty($resourceId)) {
            /** @var modResource $resource */
            $resource = $this->modx->getObject('modResource', $resourceId);
            if (!$resource) {
                throw new LexiconException('modai.error.no_resource_found');
            }
            $contextKey = $resource->get('context_key');
        }

        $stream = intval(Settings::getVisionSetting($this->modx, $field, 'stream', $namespace, true, $contextKey)) === 1;
        $model = Settings::getVisionSetting($this->modx, $field, 'model', $namespace, true, $contextKey);
        if (empty($prompt)) {
            $prompt = Settings::getVisionSetting($this->modx, $field, 'prompt', $namespace, true, $contextKey);
        }
        $customOptions = Settings::getVisionSetting($this->modx, $field, 'custom_options', $namespace, false, $contextKey);
        $maxTokens = (int)Settings::getVisionSetting($this->modx, $field, 'max_tokens', $namespace, true, $contextKey);

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
