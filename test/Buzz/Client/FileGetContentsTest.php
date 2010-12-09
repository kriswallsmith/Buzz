<?php

namespace Buzz\Client;

use Buzz\Message;

class FileGetContentsTest extends \PHPUnit_Framework_TestCase
{
    public function testSendToInvalidUrl()
    {
        $this->setExpectedException('RuntimeException');

        $request = new Message\Request();
        $request->fromUrl('http://'.substr(sha1(rand(11111, 99999)), 0, 7).':12345');

        $response = new Message\Response();

        $client = new FileGetContents();
        $client->send($request, $response);
    }
}
