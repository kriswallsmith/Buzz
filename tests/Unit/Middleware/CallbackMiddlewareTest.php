<?php

declare(strict_types=1);

namespace Buzz\Test\Unit\Middleware;

use Buzz\Exception\InvalidArgumentException;
use Buzz\Middleware\CallbackMiddleware;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;

class CallbackMiddlewareTest extends TestCase
{
    public function testCallback()
    {
        $requestIn = new Request('GET', '/');
        $responseIn = new Response(200);

        $responseOut = new Response(201);
        $requestOut = new Request('POST', '/');

        $middleware = new CallbackMiddleware(function () use ($requestIn, $responseIn, $requestOut, $responseOut) {
            $calls[] = $args = func_get_args();
            $this->assertEquals($requestIn, $args[0]);

            if (2 === count($args)) {
                $this->assertEquals($responseIn, $args[1]);

                return $responseOut;
            }

            return $requestOut;
        });

        $firstRequest = null;
        $this->assertEquals($requestOut, $middleware->handleRequest($requestIn, function ($request) {
            return $request;
        }));

        $secondRequest = null;
        $secondResponse = null;
        $this->assertEquals($responseOut, $middleware->handleResponse($requestIn, $responseIn, function ($request, $response) {
            return $response;
        }));
    }

    public function testInvalidCallback()
    {
        $this->expectException(InvalidArgumentException::class);

        new CallbackMiddleware([1, 2, 3]);
    }
}
