<?php

return new class() {
    /**
     * @var \MODX\Revolution\modX
     */
    private $modx;

    /**
     * @var int
     */
    private $action;

    /**
    * @param \MODX\Revolution\modX $modx
    * @param int $action
    * @return bool
    */
    public function __invoke(&$modx, $action)
    {
        $this->modx =& $modx;
        $this->action = $action;

        if ($this->action === \xPDO\Transport\xPDOTransport::ACTION_UNINSTALL) {
            return true;
        }

        $tools = [
            [
                'name' => \modAI\Tools\GetWeather::getSuggestedName(),
                'class' => \modAI\Tools\GetWeather::class,
                'config' => [],
                'enabled' => true,
                'default' => false,
            ]
        ];

        foreach ($tools as $tool) {
            $exists = $this->modx->getCount(\modAI\Model\Tool::class, ['name' => $tool['name']]);
            if ($exists > 0) {
                continue;
            }

            $toolObjects = $this->modx->newObject(\modAI\Model\Tool::class, $tool);
            $toolObjects->save();
        }

        $contextProviders = [
            [
                'name' => 'resources',
                'description' => 'Pinecone instance for storing resources',
                'class' => \modAI\ContextProviders\Pinecone::class,
                'config' => [
                    'endpoint' => 'ss:pinecone_url',
                    'api_key' => 'ss:pinecone_key',
                    'namespace' => 'resources',
                    'fields' => 'pagetitle,introtext,content',
                    'intro_msg' => 'Potential relevant context from resource {id}:',
                ],
                'enabled' => true,
            ]
        ];

        foreach ($contextProviders as $contextProvider) {
            $exists = $this->modx->getCount(\modAI\Model\ContextProvider::class, ['name' => $contextProvider['name']]);
            if ($exists > 0) {
                continue;
            }

            $contextProviderObjects = $this->modx->newObject(\modAI\Model\ContextProvider::class, $contextProvider);
            $contextProviderObjects->save();
        }

        $agents = [
            [
                'name' => 'RedneckWeatherMan',
                'description' => 'Presents weather in a funny form',
                'prompt' => 'You are a weather man! Report on weather in a funny redneck way.',
                'enabled' => true,
                'tools' => [
                    \modAI\Tools\GetWeather::getSuggestedName(),
                ]
            ],
            [
                'name' => 'ContentWriter',
                'description' => 'Writes a decent content',
                'prompt' => '',
                'enabled' => true,
                'tools' => [
                    \modAI\Tools\GetWeather::getSuggestedName(),
                ],
                'contextProviders' => [
                    'resources'
                ]
            ]
        ];

        foreach ($agents as $agent) {
            $exists = $this->modx->getCount(\modAI\Model\Agent::class, ['name' => $agent['name']]);
            if ($exists > 0) {
                continue;
            }

            $agentObject = $this->modx->newObject(\modAI\Model\Agent::class);
            $agentObject->set('name', $agent['name']);
            $agentObject->set('description', $agent['description']);
            $agentObject->set('enabled', $agent['enabled']);

            if (!empty($agent['prompt'])) {
                $agentObject->set('prompt', $agent['prompt']);
            }
            $agentObject->save();

            if (!empty($agent['tools'])) {
                foreach ($agent['tools'] as $toolName) {
                    $tool = $this->modx->getObject(\modAI\Model\Tool::class, ['name' => $toolName]);
                    if (!$tool) {
                        continue;
                    }

                    $agentTool = $this->modx->newObject(\modAI\Model\AgentTool::class);
                    $agentTool->set('agent_id', $agentObject->id);
                    $agentTool->set('tool_id', $tool->id);
                    $agentTool->save();
                }
            }

            if (!empty($agent['contextProviders'])) {
                foreach ($agent['contextProviders'] as $contextProviderName) {
                    $contextProvider = $this->modx->getObject(\modAI\Model\ContextProvider::class, ['name' => $contextProviderName]);
                    if (!$contextProvider) {
                        continue;
                    }

                    $agentContextProvider = $this->modx->newObject(\modAI\Model\AgentContextProvider::class);
                    $agentContextProvider->set('agent_id', $agentObject->id);
                    $agentContextProvider->set('context_provider_id', $contextProvider->id);
                    $agentContextProvider->save();
                }
            }
        }

        return true;
    }
};
