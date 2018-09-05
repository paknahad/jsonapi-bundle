<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GeneratedApiCrudControllerTest extends WebTestCase
{
    public function testNewAction()
    {
        $client = self::createClient();
        $client->request(
            'POST',
            '/sweet/foods/',
            [],
            [],
            ['Content-Type' => 'application/json'],
            '{"data": {"type": "sweet_foods","attributes": {"title": "new food"}}}'
        );
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertContains(
            '{"jsonapi":{"version":"1.0"},"links":{"self":"\/sweet\/foods\/1"},"data":{"type":"sweet_foods","id":"1","attributes":{"title":"new food"}}}',
            $client->getResponse()->getContent()
        );
    }

    public function testIndexAction()
    {
        $client = self::createClient();
        $client->request('GET', '/sweet/foods/');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertContains(
            '{"jsonapi":{"version":"1.0"},"links":{"self":"\/sweet\/foods?page[number]=1&page[size]=100","first":"\/sweet\/foods?page[number]=1&page[size]=100","last":"\/sweet\/foods?page[number]=1&page[size]=100","prev":null,"next":null},"data":[{"type":"sweet_foods","id":"1","attributes":{"title":"new food"}}]}',
            $client->getResponse()->getContent()
        );
    }
}