<?php

namespace MrAuGir\Thumbnail\Tests;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateConfiguration() : void {
        $configuration = new \Model\Configuration();
        $this->assertInstanceOf(Configuration::class, $configuration);
    }
}