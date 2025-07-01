<?php

namespace modAI\Services\Config;

use MODX\Revolution\modX;

trait Model
{
    private string $model;
    private modX $modx;

    private function __construct(string $model, modX $modx)
    {
        $this->model = $model;
        $this->modx = $modx;
    }

    public static function new(string $model, modX $modx): self
    {
        return new self($model, $modx);
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
