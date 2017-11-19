<?php

namespace Buzz\Converter;

use Buzz\Exception\LogicException;
use Buzz\Message\Response;
use Psr\Http\Message\ResponseInterface;

class ResponseConverter
{
    /**
     * @param ResponseInterface|Response $response
     * @return ResponseInterface
     */
    public static function psr7($response)
    {
        if ($response instanceof ResponseInterface) {
            return $response;
        } elseif (!$response instanceof Response) {
            throw new LogicException('Only instances of PSR7 Response and Buzz responses are allowed');
        }

        // Convert the response to psr7
        return new \GuzzleHttp\Psr7\Response(
            $response->getStatusCode(),
            $response->getHeaders(),
            $response->getContent(),
            $response->getProtocolVersion(),
            $response->getReasonPhrase()
        );
    }
    /**
     * @param ResponseInterface|Response $response
     * @return Response
     */
    public static function buzz($response)
    {
        if ($response instanceof Response) {
            return $response;
        } elseif (!$response instanceof ResponseInterface) {
            throw new LogicException('Only instances of PSR7 Response and Buzz responses are allowed');
        }

        // Convert the response to buzz response
        $headers = $response->getHeaders();
        array_unshift($headers, sprintf('HTTP/%s %d %s', $response->getProtocolVersion(), $response->getStatusCode(), $response->getReasonPhrase()));

        $buzzResponse = new Response();
        $buzzResponse->addHeaders($headers);
        $buzzResponse->setContent($response->getBody()->__toString());

        return $buzzResponse;
    }
}