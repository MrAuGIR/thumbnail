<?php

namespace MrAuGir\Thumbnail\Converter\Resolver;

use MrAuGir\Thumbnail\Converter\Converter;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class ConverterResolver
{
    /**
     * @var iterable | Converter[]
     */
    protected iterable $converters;

    /**
     * @param iterable $converters
     */
    public function __construct(
        #[TaggedIterator("mraugir.thumbnail.converter")] iterable $converters
    )
    {
        $this->converters = $converters;
    }

    /**
     * @param string $converterId
     * @return Converter|null
     */
    public function resolve(string $converterId): ?Converter
    {
        foreach ($this->converters as $converter) {
            if ($converterId == $converter->getId() ) {
                return $converter;
            }
        }
        return null;
    }
}