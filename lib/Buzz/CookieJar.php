<?php

namespace Buzz;

class CookieJar
{
  protected $cookies = array();
  protected $cookieFactory;

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
   * @param Request $request A request object
   */
  public function addCookieHeaders(Request $request)
  {
    // todo: limit to cookies that match the supplied request
    $cookies = $this->cookies;

    foreach ($cookies as $cookie)
    {
      $request->addHeader($cookie->toCookieHeader());
    }
  }

  /**
   * Processes Set-Cookie headers from a request/response pair.
   * 
   * @param Request  $request  A request object
   * @param Response $response A response object
   */
  public function processSetCookieHeaders(Request $request, Response $response)
  {
    // todo: get headers from response
    $headers = array();

    foreach ($headers as $header)
    {
      // todo: include host from request
      $cookie = new Cookie();
      $cookie->fromSetCookieHeader($header);

      $this->addCookie($cookie);
    }
  }
}
