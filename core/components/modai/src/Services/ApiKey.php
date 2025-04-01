<?php

namespace modAI\Services;

use modAI\Exceptions\LexiconException;

trait ApiKey
{
    /**
     * @return string
     * @throws LexiconException
     */
    protected function getApiKey(): string
    {
        $apiKey = $this->modx->getOption('modai.api.' . self::getServiceName() . '.key');
        if (empty($apiKey)) {
            throw new LexiconException('modai.error.invalid_api_key', ['service' => self::getServiceName()]);
        }

        return $apiKey;
    }
}
