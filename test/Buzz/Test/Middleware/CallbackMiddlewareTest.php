<?php

namespace Buzz\Test\Middleware;

use Buzz\Exception\InvalidArgumentException;
use Buzz\Middleware\CallbackMiddleware;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;

class CallbackMiddlewareTest extends TestCase
{
    public function testCallback()
    {
        $calls = array();
        $listener = new CallbackMiddleware(function() use (& $calls) {
            $calls[] = func_get_args();
        });

        $request = new Request('GET', '/');
        $response = new Response();

        $listener->handleRequest($request, function() {});
        $listener->handleResponse($request, $response, function() {});

        $this->assertEquals(array(
            array($request),
            array($request, $response),
        ), $calls);
    }

    public function testInvalidCallback()
    {
        $this->expectException(InvalidArgumentException::class);

        new CallbackMiddleware(array(1, 2, 3));
    }
}
