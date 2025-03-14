<?php

namespace modAI\API\Prompt;

use modAI\API\API;
use modAI\Exceptions\LexiconException;
use modAI\Services\AIServiceFactory;
use modAI\Services\Config\ImageConfig;
use modAI\Settings;
use Psr\Http\Message\ServerRequestInterface;

class Image extends API
{
    public function post(ServerRequestInterface $request): void
    {
        set_time_limit(0);

        $data = $request->getParsedBody();

        $prompt = $this->modx->getOption('prompt', $data);
        $field = $this->modx->getOption('field', $data, '');
        $namespace = $this->modx->getOption('namespace', $data, 'modai');

        if (empty($prompt)) {
            throw new LexiconException('modai.error.prompt_required');
        }

        $model = Settings::getImageSetting($this->modx, $field, 'model', $namespace);
        $size = Settings::getImageSetting($this->modx, $field, 'size', $namespace);
        $quality = Settings::getImageSetting($this->modx, $field, 'quality', $namespace);
        $style = Settings::getImageSetting($this->modx, $field, 'style', $namespace);
        $customOptions = Settings::getImageSetting($this->modx, $field, 'custom_options', $namespace, false);

        $aiService = AIServiceFactory::new($model, $this->modx);
        $result = $aiService->generateImage(
            $prompt,
            ImageConfig::new($model)
                ->customOptions($customOptions)
                ->size($size)
                ->quality($quality)
                ->style($style)
        );

        $this->proxyAIResponse($result);
    }

}
