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
        $url = $request->getHost().$request->getResource();

        $level = error_reporting(0);
        $content = file_get_contents($url, 0, $context);
        error_reporting($level);
        if (false === $content) {
            $error = error_get_last();
            throw new \RuntimeException($error['message']);
        }

        $http_response_header = (array)$http_response_header;
        end($http_response_header);

        while(true) {
            if (strpos(current($http_response_header), 'HTTP/') === 0) {
                break;
            } else {
                if (false === prev($http_response_header)) {
                    throw new \RuntimeException('No reason phrase found!');
                }
            }
        }
        $response->setHeaders(array_slice($http_response_header, key($http_response_header)));

        if ($response->isRedirection()) {
            throw new \RuntimeException('Maximum ('.$this->maxRedirects.') redirects followed');
        }
        $response->setContent($content);

        if ($cookieJar) {
            $cookieJar->processSetCookieHeaders($request, $response);
        }
    }
}
