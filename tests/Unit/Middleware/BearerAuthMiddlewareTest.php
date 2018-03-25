<?php

declare(strict_types=1);

namespace Buzz\Test\Unit\Middleware;

use Buzz\Middleware\BearerAuthMiddleware;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;

class BearerAuthMiddlewareTest extends TestCase
{
    public function testBearerAuthListener()
    {
        $request = new Request('GET', '/');
        $middleware = new BearerAuthMiddleware('superSecretAccessTokenGeneratedByTheNsaItself');
        $newRequest = null;
        $middleware->handleRequest($request, function ($request) use (&$newRequest) {
            $newRequest = $request;
        });

        $this->assertEquals('Bearer superSecretAccessTokenGeneratedByTheNsaItself', $newRequest->getHeaderLine('Authorization'));
    }
}
