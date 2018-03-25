<?php

declare(strict_types=1);

namespace Buzz\Util;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CookieJar
{
    /** @var Cookie[] */
    private $cookies = [];

    public function clear(): void
    {
        $this->cookies = [];
    }

    public function setCookies(array $cookies): void
    {
        $this->cookies = [];
        foreach ($cookies as $cookie) {
            $this->addCookie($cookie);
        }
    }

    /**
     * @return Cookie[]
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * Adds a cookie to the current cookie jar.
     *
     * @param Cookie $cookie A cookie object
     */
    public function addCookie(Cookie $cookie): void
    {
        $this->cookies[] = $cookie;
    }

    /**
     * Adds Cookie headers to the supplied request.
     *
     * @param RequestInterface $request A request object
     */
    public function addCookieHeaders(RequestInterface $request): RequestInterface
    {
        foreach ($this->getCookies() as $cookie) {
            if ($cookie->matchesRequest($request)) {
                $request = $request->withHeader('Cookie', $cookie->toCookieHeader());
            }
        }

        return $request;
    }

    /**
     * Processes Set-Cookie headers from a request/response pair.
     *
     * @param RequestInterface  $request  A request object
     * @param ResponseInterface $response A response object
     */
    public function processSetCookieHeaders(RequestInterface $request, ResponseInterface $response): void
    {
        $host = $request->getUri()->getHost();
        foreach ($response->getHeader('Set-Cookie') as $header) {
            $cookie = new Cookie();
            $cookie->fromSetCookieHeader($header, $host);

            $this->addCookie($cookie);
        }
    }

    /**
     * Removes expired cookies.
     */
    public function clearExpiredCookies(): void
    {
        $cookies = $this->getCookies();
        foreach ($cookies as $i => $cookie) {
            if ($cookie->isExpired()) {
                unset($cookies[$i]);
            }
        }

        $this->clear();
        $this->setCookies(array_values($cookies));
    }
}
