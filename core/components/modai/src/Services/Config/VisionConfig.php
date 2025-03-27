<?php

namespace modAI\Services\Config;

class VisionConfig
{
    use Model;
    use CustomOptions;

    private int $maxTokens;

    private bool $stream = false;

    public function stream(bool $stream): self
    {
        $this->stream = $stream;

        return $this;
    }

    public function maxTokens(int $maxTokens): self
    {
        $this->maxTokens = $maxTokens;

        return $this;
    }

    public function isStream(): bool
    {
        return $this->stream;
    }

    public function getMaxTokens(): int
    {
        return $this->maxTokens;
    }
}
