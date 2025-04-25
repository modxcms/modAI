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
    private array $binary = [];
    private bool $stream = false;
    private string $contentType;

    private function __construct(string $service, string $model, string $contentType)
    {
        $this->service = $service;
        $this->model = $model;
        $this->contentType = $contentType;
    }

    public static function new(string $service, string $model, string $contentType = 'application/json'): self
    {
        return new self($service, $model, $contentType);
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

    public function withBinary(array $binary): self
    {
        $this->binary = $binary;
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

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function getBinary(): array
    {
        return $this->binary;
    }

    public function isStream(): bool
    {
        return $this->stream;
    }
}
