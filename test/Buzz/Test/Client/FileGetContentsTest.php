<?php

namespace Buzz\Test\Client;

use Buzz\Message;
use Buzz\Client\FileGetContents;

class FileGetContentsTest extends \PHPUnit_Framework_TestCase
{
    public function testSend()
    {
        $request = new Message\Request(Message\Request::METHOD_GET, '/response.txt',__DIR__.'/fixtures');

        $response = new Message\Response();
        $client = new FileGetContents();

        $client->send($request, $response);

        $this->assertEquals(array(), $response->getHeaders());
        $this->assertEquals(file_get_contents(__DIR__.'/fixtures/response.txt'), $response->getContent());
    }
}
