<?php

namespace modAI\Services\Config;

trait CustomOptions
{
    private array $customOptions = [];

    public function customOptions($customOptions): self
    {
        if (empty($customOptions)) {
            return $this;
        }

        if (is_string($customOptions)) {
            $customOptions = json_decode($customOptions, true);
        }

        if (is_array($customOptions)) {
            $this->customOptions = $customOptions;
        }

        return $this;
    }

    public function getCustomOptions(): array
    {
        return $this->customOptions;
    }
}
