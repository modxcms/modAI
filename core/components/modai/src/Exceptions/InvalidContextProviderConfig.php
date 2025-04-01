<?php

namespace modAI\Exceptions;

use Throwable;

class InvalidContextProviderConfig extends \Exception
{
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct('Invalid Config', $code, $previous);
    }
}
