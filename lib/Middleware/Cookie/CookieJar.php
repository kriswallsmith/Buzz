<?php

declare(strict_types=1);

namespace Buzz\Middleware\Cookie;

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
     */
    public function addCookie(Cookie $cookie): void
    {
        $this->cookies[$this->getHash($cookie)] = $cookie;
    }

    /**
     * Adds Cookie headers to the supplied request.
     */
    public function addCookieHeaders(RequestInterface $request): RequestInterface
    {
        $cookies = [];
        foreach ($this->getCookies() as $cookie) {
            if ($cookie->matchesRequest($request)) {
                $cookies[] = $cookie->toCookieHeader();
            }
        }
        if ($cookies) {
            $request = $request->withAddedHeader('Cookie', implode('; ', $cookies));
        }

        return $request;
    }

    /**
     * Processes Set-Cookie headers from a request/response pair.
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

    /**
     * Create an unique identifier for the cookie. Two cookies with the same identifier
     * may have different values.
     */
    private function getHash(Cookie $cookie): string
    {
        return sha1(sprintf(
            '%s|%s|%s',
            $cookie->getName(),
            $cookie->getAttribute(Cookie::ATTR_DOMAIN),
            $cookie->getAttribute(Cookie::ATTR_PATH)
        ));
    }
}
