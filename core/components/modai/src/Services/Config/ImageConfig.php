<?php
namespace modAI\Services\Config;

class ImageConfig {
    use Model;

    private int $n = 1;
    private string $size;
    private string $quality;

    public function size(string $size): self {
        $this->size = $size;

        return $this;
    }

    public function quality(string $quality): self {
        $this->quality = $quality;

        return $this;
    }

    public function getN(): int
    {
        return $this->n;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function getQuality(): string
    {
        return $this->quality;
    }

}
