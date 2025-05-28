<?php

namespace modAI\Services\Config;

trait Options
{
    private array $options = [];

    public function options(...$options): self
    {
        if (empty($options)) {
            return $this;
        }

        $parsedOptions = [];

        foreach ($options as $option) {
            if (is_string($option)) {
                $option = json_decode($option, true);
            }

            if (is_array($option)) {
                $parsedOptions[] = $option;
            }
        }

        $this->options = array_merge(...$parsedOptions);


        return $this;
    }

    public function getOptions(?callable $parser = null): array
    {
        if ($parser) {
            return $parser($this->options);
        }

        return $this->options;
    }
}
