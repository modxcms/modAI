<?php

namespace modAI\Services\Response;

class AIResponse
{
    private string $service;
    private string $model;
    private ?int $chatId = null;
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

    public function withChatId(?int $chatId): self
    {
        $this->chatId = $chatId;
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

    public function getChatId(): ?int
    {
        return $this->chatId;
    }

    public function isStream(): bool
    {
        return $this->stream;
    }

    public function execute(): array
    {
        $headers = [];
        $boundary = '--------------------------' . microtime(true);
        $isFormData = false;
        $contentType = $this->getContentType();

        if (strtolower($contentType) === 'multipart/form-data') {
            $isFormData = true;
            $headers[] = "Content-Type:$contentType; boundary=$boundary";
        } else {
            $headers[] = "Content-Type:$contentType";
        }

        foreach ($this->getHeaders() as $key => $value) {
            $headers[] = "$key:$value";
        }

        $body = '';
        if (!$isFormData) {
            $body = json_encode($this->getBody());
        } else {
            $binary = $this->getBinary();
            foreach ($binary as $name => $files) {
                foreach ($files as $i => $file) {
                    $body .= '--' . $boundary . "\r\n";
                    $body .= 'Content-Disposition: form-data; name="'.$name.'[]"; filename="source_image_' . $i . '.png"' . "\r\n";
                    $body .= 'Content-Type: ' . $file['mimeType'] . "\r\n";
                    $body .= "\r\n";
                    $body .= base64_decode($file['base64']) . "\r\n";
                }
            }

            $input = $this->getBody();
            foreach ($input as $name => $value) {
                $body .= '--' . $boundary . "\r\n";
                $body .= 'Content-Disposition: form-data; name="' . $name . '"' . "\r\n";
                $body .= "\r\n";
                $body .= $value . "\r\n";
            }

            $body .= '--' . $boundary . '--' . "\r\n";
        }

        $ch = curl_init($this->getUrl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new \Exception($error_msg);
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $response = json_decode($response, true);

        if ($statusCode >= 400) {
            return [
                "service" => $this->getService(),
                "model" =>$this->getModel(),
                "parser" =>$this->getParser(),
                'error' => $response
            ];
        }

        return [
            "service" => $this->getService(),
            "model" =>$this->getModel(),
            "parser" =>$this->getParser(),
            'response' => $response
        ];
    }
}
