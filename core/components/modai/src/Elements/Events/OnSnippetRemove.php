<?php
namespace modAI\Elements\Events;

use MODX\Revolution\modChunk;
use MODX\Revolution\modSnippet;
use MODX\Revolution\modX;

class OnSnippetRemove extends Event
{
    public function run()
    {
        $contextName = $this->modx->getOption('modai.contexts.snippets.name');
        if (empty($contextName)) {
            return;
        }

        /** @var \modAI\Model\ContextProvider $provider */
        $provider = $this->modx->getObject(\modAI\Model\ContextProvider::class, ['enabled' => true, 'name' => $contextName, 'class' => \modAI\ContextProviders\Pinecone::class]);
        if (!$provider) {
            return;
        }

        /** @var modSnippet $snippet */
        $snippet = $this->getOption('snippet');

        try {
            /** @var \modAI\ContextProviders\Pinecone $instance */
            $instance = $provider->getContextProviderInstance();
            $instance->delete('snippet', [$snippet->id]);

        } catch (\Throwable $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[modai] context plugin: ' . $e->getMessage());
            return;
        }
    }
}
