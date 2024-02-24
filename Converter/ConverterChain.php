<?php

namespace MrAuGir\Thumbnail\Converter;

class ConverterChain
{
    /**
     * @var Converter[]
     */
    private array $chain;

    public function add(Converter $converter) : self {
        $this->chain[] = $converter;
        return $this;
    }

    /**
     * @return Converter[]
     */
    public function getChain() : array
    {
        return $this->chain;
    }
}