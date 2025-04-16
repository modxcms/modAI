<?php

namespace modAI\Elements\Events;

use MODX\Revolution\modResource;
use MODX\Revolution\modX;

class OnDocFormSave extends Event
{

    public function run()
    {
        /** @var modResource $resource */
        $resource = $this->getOption('resource');
        if ($resource->get('deleted')) {
            return;
        }

        $contextName = $this->modx->getOption('modai.contexts.resources.name');
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

            $data = $resource->toArray();
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                $data[$key] = strip_tags($value);
            }

            $instance->index('resource', $resource->get('id'), $data);
        } catch (\Throwable $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[modai] context plugin: ' . $e->getMessage());
            return;
        }
    }
}
