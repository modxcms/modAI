<?php

namespace modAI\Elements\Events;


use MODX\Revolution\modChunk;
use MODX\Revolution\modSnippet;
use MODX\Revolution\modTemplate;
use MODX\Revolution\modX;

class OnTemplateSave extends Event
{

    public function run()
    {
        /** @var modTemplate $template */
        $template = $this->getOption('template');

        $contextName = $this->modx->getOption('modai.contexts.templates.name');
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


            $data = $template->toArray();
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                $data[$key] = strip_tags($value);
            }

            $instance->index('template', $template->get('id'), $data);
        } catch (\Throwable $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[modai] context plugin: ' . $e->getMessage());
            return;
        }
    }
}
