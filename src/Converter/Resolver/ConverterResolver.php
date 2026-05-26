<?php

namespace MrAuGir\Thumbnail\Converter\Resolver;

use MrAuGir\Thumbnail\Converter\Converter;
use MrAuGir\Thumbnail\Exception\ConverterNotFoundException;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;

class ConverterResolver
{
    /**
     * @param ContainerInterface $converters Service locator of converters indexed by their id.
     */
    public function __construct(
        #[TaggedLocator("mraugir.thumbnail.converter", indexAttribute: "key")]
        private readonly ContainerInterface $converters,
    )
    {
    }

    /**
     * @param string $converterId
     * @return Converter
     * @throws ConverterNotFoundException
     */
    public function resolve(string $converterId): Converter
    {
        if (!$this->converters->has($converterId)) {
            throw new ConverterNotFoundException(sprintf("Converter with id '%s'", $converterId));
        }

        return $this->converters->get($converterId);
    }
}
