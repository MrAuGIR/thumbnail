<?php

namespace MrAuGir\Thumbnail\Converter;

use Traversable;

class ConverterChain implements \IteratorAggregate
{
    use TraitIdentifier;

    /**
     * @var Converter[]
     */
    private array $chain;

    public function __construct(string $id = null)
    {
        $this->id = $id;
    }

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

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->chain);
    }
}