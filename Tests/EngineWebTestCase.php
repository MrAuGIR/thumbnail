<?php

namespace MrAuGir\Thumbnail\Tests;

use MrAuGir\Thumbnail\Tests\Kernel\ThumbnailTest;
use Symfony\Component\HttpFoundation\Response;

class EngineWebTestCase extends ThumbnailTest
{
    public function testDefaultBrowserKit(): void
    {
        // This calls KernelTestCase::bootKernel(), and creates a
        // "client" that is acting as the browser
        $client = static::createClient();

        // Request a specific page
        $crawler = $client->request('GET', '/');

        // Validate a successful response and some content
        $this->assertResponseIsSuccessful("check if symfony browser kit is here");
    }

    public function testDynamiqueThumbnailAction() : void {
        $client = static::createClient();

        $client->request('POST', "/test/thumbnail",[], [], [], json_encode([
            'path' => "public/assets/test.jpg",
            'binary' => "convert",
            'configuration' => [
                'prefix' => 'thumb_220x20_',
                'ext' => 'jpeg',
                'options' => [
                    0 => ['name' => '-resize', 'value' => '220x20']
                ],
                'outputPath' => "/var/www/html/public/assets/thumbnail/"
            ],
        ]));

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode(),$client->getResponse()->getContent());
    }

    public function testThumbnailAction() : void {
        $client = static::createClient();

        $client->request("GET","/thumbnail/call/convert_screen_shot/public/assets/test.jpg");

        $this->assertEquals(Response::HTTP_OK,$client->getResponse()->getStatusCode());
    }
}