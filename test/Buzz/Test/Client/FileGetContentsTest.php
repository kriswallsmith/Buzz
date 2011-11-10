<?php

namespace Buzz\Test\Client;

use Buzz\Client\FileGetContents;
use Buzz\Message;

class FileGetContentsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideInvalidHosts
     */
    public function testSendToInvalidUrl($host)
    {
        $this->setExpectedException('RuntimeException');

        $request = new Message\Request();
        $request->fromUrl('http://'.$host.':12345');

        $response = new Message\Response();

        $client = new FileGetContents();
        $client->setTimeout(0.05);
        $client->send($request, $response);
    }

    public function provideInvalidHosts()
    {
        return array(
            array('invalid_domain'),
            array('invalid_domain.buzz'),
        );
    }
}
