<?php

namespace modAI\ContextProviders;

use MODX\Revolution\modX;

interface ContextProviderInterface
{
    public function __construct(modX $modx, array $config = []);

    /**
     * @param string $prompt
     * @return array<string>
     */
    public function provideContext(string $prompt): array;

    public static function getConfig(modX $modx): array;

    /**
     * Internal description
     *
     * @return string
     */
    public static function getDescription(): string;

    /**
     * The suggested name for the context provider, this will be pre-filled for the user when configuring the context provider.
     *
     * @return string
     */
    public static function getSuggestedName(): string;
}
