<?php

namespace MrAuGir\Thumbnail\Converter;

use MrAuGir\Thumbnail\Model\Configuration;

trait TraitConfiguration
{
    public Configuration $configuration;

    /**
     * @param Configuration $configuration
     * @return Converter
     */
    public function setConfiguration(Configuration $configuration): Converter
    {
        $this->configuration = $configuration;
        return $this;
    }
}