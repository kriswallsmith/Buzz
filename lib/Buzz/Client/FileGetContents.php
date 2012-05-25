<?php

namespace Buzz\Client;

use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;
use Buzz\Util\CookieJar;

class FileGetContents extends AbstractStream implements ClientInterface
{
    protected $cookieJar;

    public function __construct(CookieJar $cookieJar = null)
    {
        if ($cookieJar) {
            $this->setCookieJar($cookieJar);
        }
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
     *
     * @throws RuntimeException If file_get_contents() fires an error
     */
    public function send(RequestInterface $request, MessageInterface $response)
    {
        if ($cookieJar = $this->getCookieJar()) {
            $cookieJar->clearExpiredCookies();
            $cookieJar->addCookieHeaders($request);
        }

        $context = stream_context_create($this->getStreamContextArray($request));
        $url = $request->getHost().$request->getResource();

        $level = error_reporting(0);
        $content = file_get_contents($url, 0, $context);
        error_reporting($level);
        if (false === $content) {
            $error = error_get_last();
            throw new \RuntimeException($error['message']);
        }

        $response->setHeaders($this->filterHeaders((array) $http_response_header));
        $response->setContent($content);

        if ($cookieJar) {
            $cookieJar->processSetCookieHeaders($request, $response);
        }
    }

    private function filterHeaders(array $headers)
    {
        $filtered = array();
        foreach ($headers as $header) {
            if (0 === stripos($header, 'http/')) {
                $filtered = array();
            }

            $filtered[] = $header;
        }

        return $filtered;
    }
}
