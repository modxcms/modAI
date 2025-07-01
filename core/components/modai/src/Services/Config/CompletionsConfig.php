<?php

namespace modAI\Services\Config;

use modAI\Tools\ToolInterface;

class CompletionsConfig
{
    use Model;
    use Options;

    private string $systemInstructions = '';
    private bool $stream = false;
    private array $messages = [];
    private string $toolChoice = 'auto';
    private array $tools = [];

    public function tools(array $tools): self
    {
        $this->tools = $tools;
        return $this;
    }

    public function toolChoice(string $toolChoice): self
    {
        $this->toolChoice = $toolChoice;
        return $this;
    }

    public function systemInstructions(array $systemInstructions): self
    {
        $this->systemInstructions = implode("\n", $systemInstructions);

        return $this;
    }

    public function stream(bool $stream): self
    {
        $this->stream = $stream;

        return $this;
    }

    public function messages(array $messages): self
    {
        $this->messages = $messages;

        return $this;
    }

    public function getSystemInstructions(): string
    {
        return $this->systemInstructions;
    }

    public function isStream(): bool
    {
        return $this->stream;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return array<int, array{name: string, description: string, parameters: array}>
     */
    public function getTools(): array
    {
        $tools = [];
        foreach ($this->tools as $toolName => $tool) {
            /** @var class-string<ToolInterface> $toolClass */
            $toolClass = $tool->get('class');

            $prompt = $tool->get('prompt');
            $tools[] = [
                'name' => $toolName,
                'description' => !empty($prompt) ? $prompt : $toolClass::getPrompt($this->modx),
                'parameters' => $toolClass::getParameters($this->modx),
            ];
        }

        return $tools;
    }

    public function getToolChoice(): string
    {
        return $this->toolChoice;
    }
}
