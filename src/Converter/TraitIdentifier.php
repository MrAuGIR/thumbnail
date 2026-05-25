<?php

namespace MrAuGir\Thumbnail\Converter;

trait TraitIdentifier
{
    protected ?string $id;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }
}