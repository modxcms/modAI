<?php

namespace modAI\Services\Config;

trait Model
{
    private string $model;

    private function __construct(string $model)
    {
        $this->model = $model;
    }

    public static function new(string $model): self
    {
        return new self($model);
    }

    public function getModel(): string
    {
        if (strncmp($this->model, 'custom_', strlen('custom_')) === 0) {
            return substr($this->model, 7);
        }

        return $this->model;
    }
}
