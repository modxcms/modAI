<?php

namespace modAI\Services;

use modAI\Exceptions\LexiconException;
use modAI\Services\Config\CompletionsConfig;
use modAI\Services\Config\ImageConfig;
use modAI\Services\Config\VisionConfig;
use modAI\Services\Response\AIResponse;

interface AIService
{
    /**
     * @throws LexiconException
     */
    public function getCompletions(array $data, CompletionsConfig $config): AIResponse;

    /**
     * @throws LexiconException
     */
    public function getVision(string $prompt, string $image, VisionConfig $config): AIResponse;

    /**
     * @throws LexiconException
     */
    public function generateImage(string $prompt, ImageConfig $config): AIResponse;
}
