<?php

namespace modAI\API\Prompt;

use modAI\API\API;
use modAI\Exceptions\APIException;
use modAI\Exceptions\LexiconException;
use modAI\Services\AIServiceFactory;
use modAI\Services\Config\CompletionsConfig;
use modAI\Utils;
use Psr\Http\Message\ServerRequestInterface;

class ChatTitle extends API
{
    public function post(ServerRequestInterface $request): void
    {
        if (!$this->modx->hasPermission('modai_client_text')) {
            throw APIException::unauthorized();
        }

        set_time_limit(0);

        $data = $request->getParsedBody();

        $message = Utils::getOption('message', $data, '');

        $model = $this->modx->getOption('modai.chat.title.model');
        $prompt = $this->modx->getOption('modai.chat.title.prompt');
        $options = $this->modx->getOption('modai.chat.title.model_options');

        if (empty($model) || empty($prompt)) {
            throw new LexiconException('modai.error.prompt_required');
        }

        try {
            $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            $options = [];
        }


        $aiService = AIServiceFactory::new($model, $this->modx);
        $result = $aiService->getCompletions(
            [['content' => $message]],
            CompletionsConfig::new($model, $this->modx)
                ->options($options)
                ->systemInstructions([$prompt])
                ->stream(false)
        );

        $this->proxyAIResponse($result);
    }
}
