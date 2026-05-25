<?php

namespace MrAuGir\Thumbnail\Tests;

use MrAuGir\Thumbnail\Action\Output\ConvertImageOutput;
use PHPUnit\Framework\TestCase;

class ConvertImageOutputTest extends TestCase
{
    public function testContentTypeIsDerivedFromTheProducedFile() : void
    {
        $jpeg = realpath(__DIR__ . '/images/test.jpg');

        $response = (new ConvertImageOutput($jpeg))->getBinaryFileResponse();

        // Previously hard-coded to image/png; it must now reflect the real file (F4).
        $this->assertSame('image/jpeg', $response->headers->get('Content-Type'));
    }
}
