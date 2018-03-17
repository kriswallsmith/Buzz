<?php

namespace Buzz\Test\Middleware;

use Buzz\Middleware\BearerAuthMiddleware;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;

class BearerAuthMiddlewareTest extends TestCase
{
    public function testBearerAuthListener()
    {
        $request = new Request('GET', '/');
        $middleware = new BearerAuthMiddleware('superSecretAccessTokenGeneratedByTheNsaItself');
        $middleware->handleRequest($request, function () {});

        $this->assertEquals('Bearer superSecretAccessTokenGeneratedByTheNsaItself', $request->getHeaderLine('Authorization'));
    }
}
