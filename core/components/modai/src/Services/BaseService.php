<?php

namespace modAI\Services;

use modAI\Exceptions\LexiconException;

abstract class BaseService implements AIService
{
    /**
     * @param string|array $item - when array is passed, it's of ["type" => string, "value" => string] elements
     * @throws LexiconException
     * @return array
     */
    abstract protected function formatMessageContentItem($item): array;

    /**
     * @param string $item
     * @return mixed
     */
    protected function formatStringMessageContent(string $item)
    {
        return $item;
    }

    /**
     * @param mixed $message
     * @throws LexiconException
     * @return mixed
     */
    protected function formatUserMessageContent($message)
    {
        if (is_array($message)) {
            $formatted = [];

            foreach ($message as $item) {
                $formatted[] = $this->formatMessageContentItem($item);
            }

            return $formatted;
        }

        return $this->formatStringMessageContent($message);
    }
}
