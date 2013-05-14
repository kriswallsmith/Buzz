<?php

namespace Buzz\Test\Client;

use Buzz\Client\FileGetContents;
use Buzz\Util\CookieJar;
use Buzz\Message\Request;
use Buzz\Message\Response;
use Buzz\Message\RequestInterface;
use Buzz\Message\MessageInterface;

class FileGetContentsTest extends \PHPUnit_Framework_TestCase
{
    public function testRedirectsResolve()
    {
        $request = new Request('GET', '/ncr', 'http://google.com');
        $response = new Response();
        $client = new FileGetContents(new CookieJar());
        $client->send($request, $response);
        $this->assertEquals('http://www.google.com/', $response->getLocation());
    }
}