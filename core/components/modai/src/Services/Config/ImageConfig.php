<?php

namespace modAI\Services\Config;

class ImageConfig
{
    use Model;
    use CustomOptions;

    private int $n = 1;
    private string $size;
    private string $quality;
    private string $style;

    public function size(string $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function quality(string $quality): self
    {
        $this->quality = $quality;

        return $this;
    }

    public function style(string $style): self
    {
        $this->style = $style;

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

    public function getStyle(): string
    {
        return $this->style;
    }
}
