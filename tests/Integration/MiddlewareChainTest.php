<?php

declare(strict_types=1);

namespace Buzz\Test\Integration;

use Buzz\Browser;
use Buzz\Client\AbstractClient;
use Buzz\Middleware\MiddlewareInterface;
use Http\Client\Tests\PHPUnitUtility;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class MiddlewareChainTest extends TestCase
{
    /**
     * @dataProvider getHttpClients
     */
    public function testChainOrder(AbstractClient $client)
    {
        MyMiddleware::$hasBeenHandled = false;
        MyMiddleware::$handleCount = 0;

        $browser = new Browser($client, new Psr17Factory());
        $browser->addMiddleware(new MyMiddleware(
            function () {
                ++MyMiddleware::$handleCount;
                MyMiddleware::$hasBeenHandled = true;
                $this->assertEquals(1, MyMiddleware::$handleCount);
            },
            function () {
                $this->assertEquals(1, MyMiddleware::$handleCount);
                --MyMiddleware::$handleCount;
            }
        ));
        $browser->addMiddleware(new MyMiddleware(
            function () {
                ++MyMiddleware::$handleCount;
                $this->assertEquals(2, MyMiddleware::$handleCount);
            },
            function () {
                $this->assertEquals(2, MyMiddleware::$handleCount);
                --MyMiddleware::$handleCount;
            }
        ));
        $browser->addMiddleware(new MyMiddleware(
            function () {
                ++MyMiddleware::$handleCount;
                $this->assertEquals(3, MyMiddleware::$handleCount);
            },
            function () {
                $this->assertEquals(3, MyMiddleware::$handleCount);
                --MyMiddleware::$handleCount;
            }
        ));

        $request = new Request('GET', PHPUnitUtility::getUri());
        $browser->sendRequest($request);

        $this->assertEquals(0, MyMiddleware::$handleCount);
        $this->assertTrue(MyMiddleware::$hasBeenHandled);
    }

    public function getHttpClients()
    {
        return [
            [new \Buzz\Client\MultiCurl(new Psr17Factory(), [])],
            [new \Buzz\Client\FileGetContents(new Psr17Factory(), [])],
            [new \Buzz\Client\Curl(new Psr17Factory(), [])],
        ];
    }
}

/**
 * A test class to verify the correctness of the middleware chain.
 */
class MyMiddleware implements MiddlewareInterface
{
    public static $handleCount = 0;

    public static $hasBeenHandled = false;

    /** @var callable */
    private $requestCallable;

    /** @var callable */
    private $responseCallable;

    /**
     * @param callable $requestCallable
     * @param callable $responseCallable
     */
    public function __construct(callable $requestCallable, callable $responseCallable)
    {
        $this->requestCallable = $requestCallable;
        $this->responseCallable = $responseCallable;
    }

    public function handleRequest(RequestInterface $request, callable $next)
    {
        \call_user_func($this->requestCallable, $request);

        return $next($request);
    }

    public function handleResponse(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        \call_user_func($this->responseCallable, $request, $request);

        return $next($request, $response);
    }
}
