<?php

namespace Buzz\Client;

use Buzz\Exception\RequestException;
use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;

use Buzz\Exception\ClientException;

class FileGetContents extends AbstractStream
{
    /**
     * @see ClientInterface
     *
     * @throws ClientException If file_get_contents() fires an error
     */
    public function send(RequestInterface $request, MessageInterface $response)
    {
        $context = stream_context_create($this->getStreamContextArray($request));
        $url = $request->getHost().$request->getResource();

        $level = error_reporting(0);
        $content = file_get_contents($url, 0, $context);
        error_reporting($level);
        if (false === $content) {
            $error = error_get_last();
            $e = new RequestException($error['message']);
            $e->setRequest($request);

            throw $e;
        }

        $response->setHeaders($this->filterHeaders((array) $http_response_header));
        $response->setContent($content);
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
