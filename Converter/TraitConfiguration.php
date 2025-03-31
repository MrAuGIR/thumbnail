<?php

namespace MrAuGir\Thumbnail\Converter;

use MrAuGir\Thumbnail\Model\Configuration;

trait TraitConfiguration
{
    public Configuration $configuration;

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

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