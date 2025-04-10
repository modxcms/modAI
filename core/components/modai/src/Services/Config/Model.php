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
        $firstSlashPosition = strpos($this->model, '/');

        if ($firstSlashPosition !== false) {
            return substr($this->model, $firstSlashPosition + 1);
        }

        return $this->model;
    }

    public function getRawModel(): string
    {
        return $this->model;
    }
}
