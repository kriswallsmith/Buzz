<?php

namespace Buzz\Util;

use Buzz\Message;

class CookieJar
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
        $this->cookies[] = $cookie;
    }

    /**
     * Adds Cookie headers to the supplied request.
     *
     * @param Message\RequestInterface $request A request object
     */
    public function addCookieHeaders(Message\RequestInterface $request)
    {
        foreach ($this->cookies as $cookie) {
            if ($cookie->matchesRequest($request)) {
                $request->addHeader($cookie->toCookieHeader());
            }
        }
    }

    /**
     * Processes Set-Cookie headers from a request/response pair.
     *
     * @param Message\RequestInterface $request  A request object
     * @param Message\MessageInterface $response A response object
     */
    public function processSetCookieHeaders(Message\RequestInterface $request, Message\MessageInterface $response)
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
      foreach ($this->cookies as $i => $cookie) {
          if ($cookie->isExpired()) {
              unset($this->cookies[$i]);
          }
      }

      // reset array keys
      $this->cookies = array_values($this->cookies);
    }
}
