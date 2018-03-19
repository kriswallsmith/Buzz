<?php

namespace Buzz\Util;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CookieJar
{
    /** @var Cookie[] */
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
     * @param RequestInterface $request A request object
     */
    public function addCookieHeaders(RequestInterface $request)
    {
        foreach ($this->cookies as $cookie) {
            if ($cookie->matchesRequest($request)) {
                $request = $request->withHeader('Cookie', $cookie->toCookieHeader());
            }
        }

        return $request;
    }

    /**
     * Processes Set-Cookie headers from a request/response pair.
     *
     * @param RequestInterface $request  A request object
     * @param ResponseInterface $response A response object
     */
    public function processSetCookieHeaders(RequestInterface $request, ResponseInterface $response)
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
