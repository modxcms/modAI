<?php
namespace modAI\API\Context;

use modAI\API\API;
use modAI\Exceptions\LexiconException;
use modAI\Model\Agent;
use modAI\Model\ContextProvider;
use Psr\Http\Message\ServerRequestInterface;

class Get extends API
{
    public function post(ServerRequestInterface $request): void
    {
        $data = $request->getParsedBody();
        $prompt = $this->modx->getOption('prompt', $data);
        $agent = $this->modx->getOption('agent', $data);

        if (empty($prompt)) {
            throw new LexiconException('modai.error.prompt_required');
        }

        if (!empty($agent)) {
            $agent = $this->modx->getObject(Agent::class, ['name' => $agent]);
            if (!$agent) {
                throw new LexiconException('modai.error.invalid_agent');
            }
        }

        $contexts = [];

        $contextProviders = ContextProvider::getAvailableContextProviders($this->modx, $agent ? $agent->id : null);

        foreach ($contextProviders as $contextProvider) {
            $instance = $contextProvider->getContextProviderInstance();
            $contexts = array_merge($contexts, $instance->provideContext($prompt));
        }

        $this->success([
            'contexts' => $contexts,
        ]);
    }
}
