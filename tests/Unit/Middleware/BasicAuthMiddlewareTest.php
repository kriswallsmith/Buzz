<?php

namespace Buzz\Test\Unit\Middleware;

use Buzz\Middleware\BasicAuthMiddleware;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;

class BasicAuthMiddlewareTest extends TestCase
{
    public function testBasicAuthHeader()
    {
        $request = new Request('GET', '/');

        $middleware = new BasicAuthMiddleware('foo', 'bar');
        $newRequest = null;
        $middleware->handleRequest($request, function($request) use (&$newRequest) {
            $newRequest = $request;
        });

        $this->assertEquals('Basic '.base64_encode('foo:bar'), $newRequest->getHeaderLine('Authorization'));
    }
}
