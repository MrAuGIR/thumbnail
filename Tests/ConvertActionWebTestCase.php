<?php

namespace MrAuGir\Thumbnail\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConvertActionWebTestCase extends WebTestCase
{
    public function testCallConvert(): void
    {
        // This calls KernelTestCase::bootKernel(), and creates a
        // "client" that is acting as the browser
        $client = static::createClient();

        // Request a specific page
        $crawler = $client->request('GET', '/thumbnail/call/convert_screen_shot/https://picsum.photos/200/300');

        // Validate a successful response and some content
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode(),$client->getResponse()->getContent());
        $this->assertResponseIsSuccessful();
    }

    public function testIssueCallConvert(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/thumbnail/call/convert_unknow_shot/https://picsum.photos/200/300');

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $client->getResponse()->getStatusCode(),$client->getResponse()->getContent());
        $this->assertResponseStatusCodeSame(500);
    }

    public function testCallChainConverter(): void
    {
        $client = static::createClient();

        $crawler = $client->request(Request::METHOD_GET,'/chain/call/print_thumbnail/https://picsum.photos/200/300');

        $this->assertEquals(
            Response::HTTP_OK,
            $client->getResponse()->getStatusCode(),
            sprintf(" expect %s found %s",Response::HTTP_OK,$client->getResponse()->getStatusCode())
        );
        $this->assertResponseIsSuccessful();
    }

    public function testIssueCallChainConverter(): void
    {
        $client = static::createClient();

        $crawler = $client->request(Request::METHOD_GET,'/chain/call/unknow_chain/https://picsum.photos/200/300');

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR,"Expected 500 found ".$client->getResponse()->getStatusCode());
    }
}