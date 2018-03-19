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
        $middleware = new CallbackMiddleware(function() use (&$calls) {
            $calls[] = func_get_args();
        });

        $request = new Request('GET', '/');
        $response = new Response();

        $firstRequest = null;
        $middleware->handleRequest($request, function($request) use (&$firstRequest) {
            $firstRequest = $request;
        });

        $secondRequest = null;
        $secondResponse = null;
        $middleware->handleResponse($request, $response, function($request, $response) use (&$secondRequest, &$secondResponse) {
            $secondRequest = $request;
            $secondResponse = $response;
        });

        $this->assertEquals(array(
            array($firstRequest),
            array($secondRequest, $secondResponse),
        ), $calls);
    }

    public function testInvalidCallback()
    {
        $this->expectException(InvalidArgumentException::class);

        new CallbackMiddleware(array(1, 2, 3));
    }
}
