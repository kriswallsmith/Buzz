<?php

namespace Buzz;

class FileGetContentsClient extends AbstractStreamClient implements ClientInterface
{
  protected $cookieJar;

  public function __construct(CookieJar $cookieJar = null)
  {
    $this->setCookieJar($cookieJar);
  }

  public function setCookieJar(CookieJar $cookieJar)
  {
    $this->cookieJar = $cookieJar;
  }

  public function getCookieJar()
  {
    return $this->cookieJar;
  }

  /**
   * @see ClientInterface
   */
  public function send(Request $request, Response $response)
  {
    if ($cookieJar = $this->getCookieJar())
    {
      $cookieJar->clearExpiredCookies();
      $cookieJar->addCookieHeaders($request);
    }

    $context = stream_context_create(static::getStreamContextArray($request));
    $content = file_get_contents($request->getUrl(), 0, $context);

    $response->setHeaders($http_response_header);
    $response->setContent($content);

    if ($cookieJar)
    {
      $cookieJar->processSetCookieHeaders($request, $response);
    }
  }
}
