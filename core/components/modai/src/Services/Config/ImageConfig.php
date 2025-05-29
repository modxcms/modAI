<?php

namespace modAI\Services\Config;

class ImageConfig
{
    use Model;
    use Options;

    private array $attachments;

    public function attachments($attachments): self
    {
        if (!is_array($attachments)) {
            $this->attachments = [];
            return $this;
        }

        $this->attachments = $attachments;

        return $this;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }
}
