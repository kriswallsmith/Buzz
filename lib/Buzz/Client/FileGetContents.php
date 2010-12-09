<?php

namespace Buzz\Client;

use Buzz\Cookie;
use Buzz\Message;

class FileGetContents extends AbstractStream implements ClientInterface
{
    protected $cookieJar;

    public function __construct(Cookie\Jar $cookieJar = null)
    {
        if ($cookieJar) {
            $this->setCookieJar($cookieJar);
        }
    }

    public function setCookieJar(Cookie\Jar $cookieJar)
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
    public function send(Message\Request $request, Message\Response $response)
    {
        if ($cookieJar = $this->getCookieJar()) {
            $cookieJar->clearExpiredCookies();
            $cookieJar->addCookieHeaders($request);
        }

        $context = stream_context_create($this->getStreamContextArray($request));

        try {
            $content = file_get_contents($request->getUrl(), 0, $context);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), null, $e);
        }

        $response->setHeaders((array) $http_response_header);
        $response->setContent($content);

        if ($cookieJar) {
            $cookieJar->processSetCookieHeaders($request, $response);
        }
    }
}
