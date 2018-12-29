<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GeneratedApiCRUDTest extends WebTestCase
{
    /** @dataProvider provideAuthor */
    public function testNewAuthorAction($author)
    {
        static $id;
        $id++;

        $client = self::createClient();

        $client->insulate();
        $client->request(
            'POST',
            '/authors/',
            [],
            [],
            ['Content-Type' => 'application/json'],
            '{"data": {"type": "authors","attributes": {"name": "'.$author.'"}}}'
        );
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertContains(
            '{"jsonapi":{"version":"1.0"},"links":{"self":"\/authors\/'.$id.'"},"data":{"type":"authors","id":"'.$id.'","attributes":{"name":"'.$author.'"},"relationships":{"books":{"data":[]}}}}',
            $client->getResponse()->getContent()
        );
    }

    /** @dataProvider provide‌Book */
    public function testNewBookAction($book, $authorId)
    {
        static $id;
        $id++;

        $client = self::createClient();

        $client->insulate();
        $client->request(
            'POST',
            '/books/',
            [],
            [],
            ['Content-Type' => 'application/json'],
            '{
                "data": {
                    "type": "books",
                    "attributes": {
                        "title": "'.$book.'"
                    },
                    "relationships": {
                        "authors": {
                            "data": [
                                {
                                    "type": "authors",
                                    "id": "'.$authorId.'"
                                }
                            ]
                        }
                    }
                }
            }'
        );
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertContains(
            '{"jsonapi":{"version":"1.0"},"links":{"self":"\/books\/'.$id.'"},"data":{"type":"books","id":"'.$id.'","attributes":{"title":"'.$book.'"},"relationships":{"authors":{"data":[{"type":"authors","id":"'.$authorId.'"}]}}}}',
            $client->getResponse()->getContent()
        );
    }

    public function testEditAction()
    {
        $client = self::createClient();
        $client->insulate();
        $client->request(
            'PATCH',
            '/authors/2',
            [],
            [],
            ['Content-Type' => 'application/json'],
            '{"data": {"type": "authors","id":"2","attributes": {"name": "Mr Aldous Huxley"}}}'
        );
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertContains(
            '{"jsonapi":{"version":"1.0"},"links":{"self":"\/authors\/2"},"data":{"type":"authors","id":"2","attributes":{"name":"Mr Aldous Huxley"},"relationships":{"books":{"data":[{"type":"books","id":"3"}]}}}}',
            $client->getResponse()->getContent()
        );
    }

    public function testGetAction()
    {
        $client = self::createClient();
        $client->insulate();
        $client->request(
            'GET',
            '/authors/2?include=books'
        );
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertContains(
            '{"jsonapi":{"version":"1.0"},"links":{"self":"\/authors\/2"},"data":{"type":"authors","id":"2","attributes":{"name":"Mr Aldous Huxley"},"relationships":{"books":{"data":[{"type":"books","id":"3"}]}}},"included":[{"type":"books","id":"3","attributes":{"title":"Brave New World"},"relationships":{"authors":{"data":[{"type":"authors","id":"2"}]}}}]}',
            $client->getResponse()->getContent()
        );
    }

    public function testIndexAction()
    {
        $client = self::createClient();
        $client->insulate();
        $client->request('GET', '/authors/');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertContains(
            '{"jsonapi":{"version":"1.0"},"links":{"self":"\/authors?page[number]=1&page[size]=100","first":"\/authors?page[number]=1&page[size]=100","last":"\/authors?page[number]=1&page[size]=100","prev":null,"next":null},"data":[{"type":"authors","id":"1","attributes":{"name":"George Orwell"},"relationships":{"books":{"data":[{"type":"books","id":"1"},{"type":"books","id":"2"}]}}},{"type":"authors","id":"2","attributes":{"name":"Mr Aldous Huxley"},"relationships":{"books":{"data":[{"type":"books","id":"3"}]}}}]}',
            $client->getResponse()->getContent()
        );
    }

    public function testDeleteAction()
    {
        $client = self::createClient();
        $client->request(
            'DELETE',
            '/authors/1'
        );
        $this->assertTrue($client->getResponse()->isSuccessful());
    }


    public function provideAuthor()
    {
        yield['George Orwell'];
        yield['Aldous Huxley'];
    }

    public function provide‌Book()
    {
        yield['Nineteen Eighty-Four', 1];
        yield['Animal Farm', 1];
        yield['Brave New World', 2];
    }
}
