<?php

namespace modAI\Services\Response;

class AIResponse
{
    private string $service;
    private string $model;
    private string $url;
    private string $parser;
    private array $headers = [];
    private array $body = [];
    private bool $stream = false;

    private function __construct(string $service, string $model)
    {
        $this->service = $service;
        $this->model = $model;
    }

    public static function new(string $service, string $model): self
    {
        return new self($service, $model);
    }

    public function withUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function withHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    public function withBody(array $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function withParser(string $parser): self
    {
        $this->parser = $parser;
        return $this;
    }

    public function withStream(bool $stream): self
    {
        $this->stream = $stream;
        return $this;
    }

    public function getService(): string
    {
        return $this->service;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getParser(): string
    {
        return $this->parser;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): string
    {
        return json_encode($this->body);
    }

    public function isStream(): bool
    {
        return $this->stream;
    }
}
