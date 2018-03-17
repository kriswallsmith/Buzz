<?php

namespace Buzz\Test\Middleware;

use Buzz\Middleware\BasicAuthMiddleware;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;

class BasicAuthMiddlewareTest extends TestCase
{
    public function testBasicAuthHeader()
    {
        $request = new Request('GET', '/');

        $middleware = new BasicAuthMiddleware('foo', 'bar');
        $middleware->handleRequest($request, function() {});

        $this->assertEquals('Basic '.base64_encode('foo:bar'), $request->getHeaderLine('Authorization'));
    }
}
