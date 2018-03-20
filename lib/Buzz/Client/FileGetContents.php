<?php

declare(strict_types=1);

namespace Buzz\Client;

use Buzz\Converter\HeaderConverter;
use Buzz\Exception\RequestException;
use Nyholm\Psr7\Factory\MessageFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class FileGetContents extends AbstractClient implements BuzzClientInterface
{
    public function sendRequest(RequestInterface $request, array $options = []): ResponseInterface
    {
        $options = $this->validateOptions($options);
        $context = stream_context_create($this->getStreamContextArray($request, $options));

        $level = error_reporting(0);
        $content = file_get_contents($request->getUri()->__toString(), false, $context);
        error_reporting($level);
        if (false === $content) {
            $error = error_get_last();
            throw new RequestException($request, $error['message']);
        }

        $filteredHeaders = $this->filterHeaders((array) $http_response_header);
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

    /**
     * Converts a request into an array for stream_context_create().
     *
     * @param RequestInterface $request A request object
     * @param ParameterBag $options
     *
     * @return array An array for stream_context_create()
     */
    private function getStreamContextArray(RequestInterface $request, ParameterBag $options): array
    {
        $headers = $request->getHeaders();
        unset($headers['Host']);
        $options = array(
            'http' => array(
                // values from the request
                'method'           => $request->getMethod(),
                'header'           => implode("\r\n", HeaderConverter::toBuzzHeaders($headers)),
                'content'          => $request->getBody()->__toString(),
                'protocol_version' => $request->getProtocolVersion(),

                // values from the current client
                'ignore_errors'    => true,
                'follow_location'  => $options->get('follow_redirects'),
                'max_redirects'    => $options->get('max_redirects') + 1,
                'timeout'          => $options->get('timeout'),
            ),
            'ssl' => array(
                'verify_peer'      => $options->get('verify_peer'),
                'verify_host'      => $options->get('verify_host'),
            ),
        );

        if (null !== $options->get('proxy')) {
            $options['http']['proxy'] = $options->get('proxy');
            $options['http']['request_fulluri'] = true;
        }

        return $options;
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
