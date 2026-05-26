<?php

namespace MrAuGir\Thumbnail\Converter\Resolver;

use MrAuGir\Thumbnail\Converter\ConverterChain;
use MrAuGir\Thumbnail\Exception\ConverterNotFoundException;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;

class ConverterChainResolver
{
    /**
     * @param ContainerInterface $chains Service locator of converter chains indexed by their id.
     */
    public function __construct(
        #[TaggedLocator("mraugir.thumbnail.chain", indexAttribute: "key")]
        private readonly ContainerInterface $chains,
    )
    {
    }

    /**
     * @param string $chainId
     * @return ConverterChain
     * @throws ConverterNotFoundException
     */
    public function resolve(string $chainId): ConverterChain
    {
        if (!$this->chains->has($chainId)) {
            throw new ConverterNotFoundException(sprintf("Converter with id '%s'", $chainId));
        }

        return $this->chains->get($chainId);
    }
}
