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

    public static function getConfig(): array;
}
