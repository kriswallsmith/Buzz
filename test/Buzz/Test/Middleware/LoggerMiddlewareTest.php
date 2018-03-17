<?php

namespace Buzz\Test\Middleware;

use Buzz\Exception\InvalidArgumentException;
use Buzz\Middleware\LoggerMiddleware;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;

class LoggerMiddlewareTest extends TestCase
{
    public function testLogger()
    {
        $test = $this;

        // TODO Use PSR3 logger
        $logger = function($line) use ($test) {
            $test->assertRegExp('~^Sent "GET http://google.com/" in \d+ms$~', $line);
        };

        $request = new Request('GET', 'http://google.com/');
        $response = new Response();

        $middleware = new LoggerMiddleware($logger);
        $middleware->handleRequest($request, function() {});
        $middleware->handleResponse($request, $response, function() {});
    }

    public function testInvalidLogger()
    {
        $this->expectException(InvalidArgumentException::class);

        new LoggerMiddleware(array(1, 2, 3));
    }
}
