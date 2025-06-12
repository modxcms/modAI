<?php

namespace modAI\Config;

class ConfigBuilder
{
    protected $translateFn = null;
    protected $nameTemplate = null;
    protected $descriptionTemplate = null;

    protected array $config = [];

    private function __construct($modx = null, string $nameTemplate = null, string $descriptionTemplate = null)
    {
        if ($modx !== null) {
            $this->translateFn = function (string $key) use ($modx) {
                return $modx->lexicon($key);
            };

            $this->nameTemplate = $nameTemplate;
            $this->descriptionTemplate = $descriptionTemplate;
        }
    }

    public static function new($modx = null, string $nameTemplate = null, string $descriptionTemplate = null): self
    {
        return new static($modx, $nameTemplate, $descriptionTemplate);
    }

    public function addField(string $key, callable $callback = null): self
    {
        $fieldBuilder = new FieldBuilder($key, $this->translateFn, $this->nameTemplate, $this->descriptionTemplate);

        if (is_callable($callback)) {
            $callback($fieldBuilder);
        }

        $this->config[$key] = $fieldBuilder->build();
        return $this;
    }

    public function build(): array
    {
        return $this->config;
    }
}
