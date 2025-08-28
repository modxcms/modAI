<?php
namespace modAI\Config;

class FieldBuilder
{
    protected array $field = [
        'type' => 'textfield',
    ];

    protected string $key;

    public function __construct(string $key, callable $translateFn = null, string $nameTemplate = null, string $descriptionTemplate = null)
    {
        $this->key = $key;

        if (is_callable($translateFn)) {
            if (!empty($nameTemplate)) {
                $this->name($translateFn(str_replace('{key}', $key, $nameTemplate)));
            }

            if (!empty($descriptionTemplate)) {
                $this->description($translateFn(str_replace('{key}', $key, $descriptionTemplate)));
            }
        }
    }

    public function name(string $name): self
    {
        $this->field['name'] = $name;
        return $this;
    }

    public function description(string $description): self
    {
        $this->field['description'] = $description;
        return $this;
    }

    public function required(bool $required = true): self
    {
        $this->field['required'] = $required;
        return $this;
    }

    public function type(string $type): self
    {
        $this->field['type'] = $type;
        return $this;
    }

    public function extra(string $key, $value): self
    {
        if (!isset($this->field['extraProperties'])) {
            $this->field['extraProperties'] = [];
        }

        $this->field['extraProperties'][$key] = $value;
        return $this;
    }

    public function default($default): self
    {
        $this->field['defaultValue'] = $default;

        return $this;
    }

    public function build(): array
    {
        return $this->field;
    }
}
