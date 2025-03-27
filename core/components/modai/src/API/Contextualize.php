<?php
namespace modAI\API\Prompt;

use modAI\API\API;
use modAI\Exceptions\LexiconException;
use modAI\Services\AIServiceFactory;
use modAI\Services\Config\CompletionsConfig;
use modAI\Settings;
use Psr\Http\Message\ServerRequestInterface;

class Contextualize extends API
{
    public function post(ServerRequestInterface $request): void
    {
        set_time_limit(0);

        $data = $request->getParsedBody();

        $prompt = $this->modx->getOption('prompt', $data);
        $agent = $this->modx->getOption('agent', $data);
        if (empty($prompt)) {
            throw new LexiconException('modai.error.prompt_required');
        }
        
        //@todo implement vector database

        $message = '';

        $this->success([
            'message' => $message,
        ]);
    }
}
