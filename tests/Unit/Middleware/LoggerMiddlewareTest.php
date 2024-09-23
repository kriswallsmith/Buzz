<?php

declare(strict_types=1);

namespace Buzz\Test\Unit\Middleware;

use Buzz\Middleware\LoggerMiddleware;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class LoggerMiddlewareTest extends TestCase
{
    public function testLogger()
    {
        $that = $this;

        // TODO Use PSR3 logger
        $logger = new CallbackLogger(function ($level, $message, array $context) use ($that) {
            $pattern = '~^Sent "GET http://google.com/" in \d+ms$~';
            if (method_exists($this, 'assertMatchesRegularExpression')) {
                $that->assertMatchesRegularExpression($pattern, $message);

                return;
            }
            // phpunit 7 compatibility
            $that->assertRegExp($pattern, $message);
        });

        $request = new Request('GET', 'http://google.com/');
        $response = new Response();

        $middleware = new LoggerMiddleware($logger);
        $middleware->handleRequest($request, function () {
        });
        $middleware->handleResponse($request, $response, function () {
        });
    }
}

class CallbackLogger implements LoggerInterface
{
    use LoggerTrait;

    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function log($level, $message, array $context = [])
    {
        $f = $this->callback;
        $f($level, $message, $context);
    }
}
