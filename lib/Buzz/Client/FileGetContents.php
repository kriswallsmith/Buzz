<?php

declare(strict_types=1);

namespace Buzz\Client;

use Buzz\Converter\HeaderConverter;
use Buzz\Exception\RequestException;
use Nyholm\Psr7\Factory\MessageFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class FileGetContents extends AbstractStream implements ClientInterface
{
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $context = stream_context_create($this->getStreamContextArray($request));

        $level = error_reporting(0);
        $content = file_get_contents($request->getUri()->__toString(), false, $context);
        error_reporting($level);
        if (false === $content) {
            $error = error_get_last();
            throw new RequestException($request, $error['message']);
        }

        $filteredHeaders = $this->filterHeaders((array)$http_response_header);
        $statusLine = array_shift($filteredHeaders);
        list($protocolVersion, $statusCode, $reasonPhrase) = $this->parseStatusLine($statusLine);

        $response = (new MessageFactory())->createResponse(
            $statusCode,
            $reasonPhrase,
            HeaderConverter::toPsrHeaders($filteredHeaders),
            $content,
            $protocolVersion
        );

        $response->getBody()->rewind();

        return $response;
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
