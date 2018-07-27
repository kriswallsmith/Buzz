<?php

declare(strict_types=1);

namespace Buzz\Test\Unit\Middleware;

use Buzz\Middleware\ContentTypeMiddleware;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class ContentTypeMiddlewareTest extends TestCase
{
    public function testMiddleware()
    {   

        // XML
        $request = new Request('GET', 
                                'http://foo.com', 
                                [], 
                                '<?xml version="1.0" encoding="UTF-8"?>
                                <note>
                                  <to>Lorem</to>
                                  <from>Ipsum</from>
                                  <heading>Lorem</heading>
                                  <body>Ipsum</body>
                                </note>');

        /** @var RequestInterface $updatedRequest */
        $updatedRequest = null;
        $middleware = new ContentTypeMiddleware();
        $middleware->handleRequest($request, function (RequestInterface $request) use (&$updatedRequest) {
            $updatedRequest = $request;
        });

        $this->assertEquals('application/xml', $updatedRequest->getHeaderLine('Content-Type'));

        //JSON
        $request = new Request('GET', 
                                'http://foo.com', 
                                [],
                                '{
                                  "userId": 1,
                                  "id": 1,
                                  "title": "sunt aut facere repellat provident occaecati excepturi optio reprehenderit",
                                  "body": "quia et suscipit\nsuscipit recusandae consequuntur expedita et cum\nreprehenderit molestiae ut ut quas totam\nnostrum rerum est autem sunt rem eveniet architecto"
                                }');

        /** @var RequestInterface $updatedRequest */
        $updatedRequest = null;
        $middleware = new ContentTypeMiddleware();
        $middleware->handleRequest($request, function (RequestInterface $request) use (&$updatedRequest) {
            $updatedRequest = $request;
        });

        $this->assertEquals('application/json', $updatedRequest->getHeaderLine('Content-Type'));

        
    }
}
