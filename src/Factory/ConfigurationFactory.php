<?php

namespace MrAuGir\Thumbnail\Factory;

use MrAuGir\Thumbnail\Model\Configuration;

class ConfigurationFactory
{
    /**
     * @param array $data
     * @return Configuration
     */
    public static function create(array $data) : Configuration {
        $configuration = new Configuration();
        if (isset($data['options'])) {
            self::addOptions($configuration,$data['options']);
        }

        if (isset($data['outputPath'])) {
            $configuration->setOutputPath($data['outputPath']);
        }

        if (isset($data['prefix'])) {
            $configuration->setPrefix($data['prefix']);
        }

        if (isset($data['ext'])) {
            $configuration->setExtension($data['ext']);
        }

        return $configuration;
    }

    /**
     * @param Configuration $configuration
     * @param array $options
     * @return void
     */
    private static function addOptions(Configuration $configuration, array $options) : void {
        foreach ($options as $option) {
            if (!is_array($option)) {
                continue;
            }
            $configuration->addOption(OptionFactory::create($option));
        }
    }
}