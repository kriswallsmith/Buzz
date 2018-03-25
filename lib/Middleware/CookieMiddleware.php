<?php

namespace Buzz\Middleware;

use Buzz\Util\Cookie;
use Buzz\Util\CookieJar;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CookieMiddleware implements MiddlewareInterface
{
    private $cookieJar;

    public function __construct()
    {
        $this->cookieJar = new CookieJar();
    }

    public function setCookies($cookies)
    {
        $this->cookieJar->setCookies($cookies);
    }

    public function getCookies()
    {
        return $this->cookieJar->getCookies();
    }

    /**
     * Adds a cookie to the current cookie jar.
     *
     * @param Cookie $cookie A cookie object
     */
    public function addCookie(Cookie $cookie)
    {
        $this->cookieJar->addCookie($cookie);
    }

    public function handleRequest(RequestInterface $request, callable $next)
    {
        $this->cookieJar->clearExpiredCookies();
        $request = $this->cookieJar->addCookieHeaders($request);

        return $next($request);
    }

    public function handleResponse(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $this->cookieJar->processSetCookieHeaders($request, $response);

        return $next($request, $response);
    }
}