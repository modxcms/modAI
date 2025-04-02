<?php

namespace modAI\Exceptions;

use Throwable;

class APIException extends \Exception
{
    private int $statusCode = 400;
    private string $title;
    private string $detail;

    public function __construct(int $statusCode, string $title, string $detail, $code = 0, Throwable $previous = null)
    {
        $this->statusCode = $statusCode;
        $this->title = $title;
        $this->detail = $detail;

        parent::__construct($title, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDetail(): string
    {
        return $this->detail;
    }

    public static function methodNotAllowed($code = 0, Throwable $previous = null): self
    {
        return new self(
            405,
            'Method Not Allowed',
            'The requested method is not supported by this resource.',
            $code,
            $previous,
        );
    }

    public static function badRequest($code = 0, Throwable $previous = null): self
    {
        return new self(
            400,
            'Bad Request',
            'The request could not be understood by the server due to malformed syntax. DO NOT repeat the request without modifications.',
            $code,
            $previous,
        );
    }

    public static function notFound($code = 0, Throwable $previous = null): self
    {
        return new self(
            404,
            'Not Found',
            'The requested resource was not found on this server.',
            $code,
            $previous,
        );
    }

    public static function unauthorized($code = 0, Throwable $previous = null): self
    {
        return new self(
            401,
            'Unauthorized',
            'The requested resource requires authentication.',
            $code,
            $previous,
        );
    }
}
