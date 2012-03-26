<?php

namespace Buzz\Client;

use Buzz\Util;
use Buzz\Message;

class FileGetContents extends AbstractStream implements ClientInterface
{
    protected $cookieJar;

    public function __construct(Cookie\CookieJar $cookieJar = null)
    {
        if ($cookieJar) {
            $this->setCookieJar($cookieJar);
        }
    }

    public function setCookieJar(Cookie\CookieJar $cookieJar)
    {
        $this->cookieJar = $cookieJar;
    }

    public function getCookieJar()
    {
        return $this->cookieJar;
    }

    /**
     * @see ClientInterface
     *
     * @throws RuntimeException If file_get_contents() fires an error
     */
    public function send(Message\RequestInterface $request, Message\MessageInterface $response)
    {
        if ($cookieJar = $this->getCookieJar()) {
            $cookieJar->clearExpiredCookies();
            $cookieJar->addCookieHeaders($request);
        }

        $context = stream_context_create($this->getStreamContextArray($request));

        $content = @file_get_contents($request->getHost().$request->getResource(), 0, $context);
        if (false === $content) {
            $error = error_get_last();
            throw new \RuntimeException($error['message']);
        }

        $response->setHeaders((array) $http_response_header);
        $response->setContent($content);

        if ($cookieJar) {
            $cookieJar->processSetCookieHeaders($request, $response);
        }
    }
}
