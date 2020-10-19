<?php

declare(strict_types=1);

namespace Buzz\Test\Unit\Middleware;

use Buzz\Middleware\WsseAuthMiddleware;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;

class WsseAuthMiddlewareTest extends TestCase
{
    public function testBasicAuthHeader()
    {
        $request = new Request('GET', '/');

        $middleware = new WsseAuthMiddleware('foo', 'bar');
        $newRequest = null;
        $middleware->handleRequest($request, function ($request) use (&$newRequest) {
            $newRequest = $request;
        });

        $this->assertEquals('WSSE profile="UsernameToken"', $newRequest->getHeaderLine('Authorization'));
        $wsse = $newRequest->getHeaderLine('X-WSSE');
        $pattern = '|UsernameToken Username="foo", PasswordDigest=".+?", Nonce=".+?", Created=".+?"|';
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression($pattern, $wsse);

            return;
        }
        // phpunit 7 compatibility
        $this->assertRegExp($pattern, $wsse);
    }
}
