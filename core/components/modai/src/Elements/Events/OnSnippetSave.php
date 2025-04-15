<?php

namespace modAI\Elements\Events;


use MODX\Revolution\modChunk;
use MODX\Revolution\modSnippet;
use MODX\Revolution\modX;

class OnSnippetSave extends Event
{

    public function run()
    {
        /** @var modSnippet $snippet */
        $snippet = $this->getOption('snippet');

        $contextName = $this->modx->getOption('modai.contexts.snippets.name');
        if (empty($contextName)) {
            return;
        }

        /** @var \modAI\Model\ContextProvider $provider */
        $provider = $this->modx->getObject(\modAI\Model\ContextProvider::class, ['enabled' => true, 'name' => $contextName, 'class' => \modAI\ContextProviders\Pinecone::class]);
        if (!$provider) {
            return;
        }

        try {
            /** @var \modAI\ContextProviders\Pinecone $instance */
            $instance = $provider->getContextProviderInstance();


            $data = $snippet->toArray();
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                $data[$key] = strip_tags($value);
            }

            $instance->index('snippet', $snippet->get('id'), $data);
        } catch (\Throwable $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[modai] context plugin: ' . $e->getMessage());
            return;
        }
    }
}
