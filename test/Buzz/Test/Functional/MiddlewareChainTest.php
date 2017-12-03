<?php

namespace Buzz\Test\Functional;

use Buzz\Browser;
use Buzz\Middleware\MiddlewareInterface;
use GuzzleHttp\Psr7\Request;
use Http\Client\Tests\PHPUnitUtility;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class MiddlewareChainTest extends TestCase
{
    public function testChainOrder()
    {
        $browser = new Browser();
        $browser->addMiddleware(new MyMiddleware(
            function () {
                MyMiddleware::$handleCount++;
                MyMiddleware::$hasBeenHandled = true;
                $this->assertEquals(1, MyMiddleware::$handleCount);
            },
            function () {
                $this->assertEquals(1, MyMiddleware::$handleCount);
                MyMiddleware::$handleCount--;
            }
        ));
        $browser->addMiddleware(new MyMiddleware(
            function () {
                MyMiddleware::$handleCount++;
                $this->assertEquals(2, MyMiddleware::$handleCount);
            },
            function () {
                $this->assertEquals(2, MyMiddleware::$handleCount);
                MyMiddleware::$handleCount--;
            }
        ));
        $browser->addMiddleware(new MyMiddleware(
            function () {
                MyMiddleware::$handleCount++;
                $this->assertEquals(3, MyMiddleware::$handleCount);
            },
            function () {
                $this->assertEquals(3, MyMiddleware::$handleCount);
                MyMiddleware::$handleCount--;
            }
        ));

        $request = new Request('GET', PHPUnitUtility::getUri());
        $browser->sendRequest($request);

        $this->assertEquals(0, MyMiddleware::$handleCount);
        $this->assertTrue(MyMiddleware::$hasBeenHandled);

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
        call_user_func($this->requestCallable, $request);

        return $next($request);
    }

    public function handleResponse(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        call_user_func($this->responseCallable, $request, $request);

        return $next($request, $response);
    }
}
