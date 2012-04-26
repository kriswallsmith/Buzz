<?php

namespace Buzz\Cookie;

use Buzz\Message;

class Jar
{
    protected $cookies = array();

    public function setCookies($cookies)
    {
        $this->cookies = array();
        foreach ($cookies as $cookie) {
            $this->addCookie($cookie);
        }
    }

    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * Adds a cookie to the current cookie jar.
     *
     * @param Cookie $cookie A cookie object
     */
    public function addCookie(Cookie $cookie)
    {
        $cookies = $this->getCookies();
        $cookies[] = $cookie;
        $this->setCookies($cookies);
    }

    /**
     * Adds Cookie headers to the supplied request.
     *
     * @param Message\Request $request A request object
     */
    public function addCookieHeaders(Message\Request $request)
    {
        foreach ($this->getCookies() as $cookie) {
            if ($cookie->matchesRequest($request)) {
                $request->addHeader($cookie->toCookieHeader());
            }
        }
    }

    /**
     * Processes Set-Cookie headers from a request/response pair.
     *
     * @param Message\Request  $request  A request object
     * @param Message\Response $response A response object
     */
    public function processSetCookieHeaders(Message\Request $request, Message\Response $response)
    {
        foreach ($response->getHeader('Set-Cookie', false) as $header) {
            $cookie = new Cookie();
            $cookie->fromSetCookieHeader($header, parse_url($request->getHost(), PHP_URL_HOST));

            $this->addCookie($cookie);
        }
    }

    /**
     * Removes expired cookies.
     */
    public function clearExpiredCookies()
    {
        $cookies = $this->getCookies();
        foreach ($cookies as $i => $cookie) {
            if ($cookie->isExpired()) {
                unset($cookies[$i]);
            }
        }

        // reset array keys
        $this->setCookies(array_values($cookies));
    }
}
