<?php

namespace MrAuGir\Thumbnail\Converter\Resolver;

use MrAuGir\Thumbnail\Converter\ConverterChain;
use MrAuGir\Thumbnail\Exception\ConverterNotFoundException;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class ConverterChainResolver
{
    /**
     * @var iterable | ConverterChain[]
     */
    protected iterable $chains;

    /**
     * @param iterable $chains
     */
    public function __construct(
        #[TaggedIterator("mraugir.thumbnail.chain")] iterable $chains
    )
    {
        $this->chains = $chains;
    }

    /**
     * @param string $chainId
     * @return ConverterChain
     * @throws ConverterNotFoundException
     */
    public function resolve(string $chainId): ConverterChain
    {
        foreach ($this->chains as $chain) {
            if ($chainId == $chain->getId() ) {
                return $chain;
            }
        }
        throw new ConverterNotFoundException(sprintf("Converter with id '%s'",$chainId));
    }
}