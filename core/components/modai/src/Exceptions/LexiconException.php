<?php

namespace modAI\Exceptions;

use Throwable;

class LexiconException extends \Exception
{
    private string $lexicon;
    private array $lexiconParams = [];

    public function __construct($lexicon = "", array $lexiconParams = [], $code = 0, Throwable $previous = null)
    {
        $this->lexicon = $lexicon;
        $this->lexiconParams = $lexiconParams;

        parent::__construct($lexicon, $code, $previous);
    }

    public function getLexicon(): string
    {
        return $this->lexicon;
    }

    public function getLexiconParams(): array
    {
        return $this->lexiconParams;
    }
}
