<?php

declare(strict_types=1);

namespace Buzz\Message;

use Buzz\Exception\InvalidArgumentException;
use Http\Message\ResponseFactory as HTTPlugResponseFactory;
use Interop\Http\Factory\ResponseFactoryInterface as InteropResponseFactory;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ResponseBuilder
{
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @param HTTPlugResponseFactory|InteropResponseFactory $responseFactory
     */
    public function __construct($responseFactory)
    {
        if (!$responseFactory instanceof HTTPlugResponseFactory && !$responseFactory instanceof InteropResponseFactory) {
            throw new InvalidArgumentException('First parameter to ResponseBuilder must be a response factory');
        }

        $this->response = $responseFactory->createResponse();
    }

    public function setStatus(string $input): void
    {
        $parts = explode(' ', $input, 3);
        if (count($parts) < 2 || 0 !== strpos(strtolower($parts[0]), 'http/')) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid HTTP status line', $input));
        }

        $this->response = $this->response->withStatus((int) $parts[1], isset($parts[2]) ? $parts[2] : '');
        $this->response = $this->response->withProtocolVersion((string) substr($parts[0], 5));
    }

    /**
     * Add a single HTTP header line.
     *
     * @param string $input
     */
    public function addHeader(string $input): void
    {
        list($key, $value) = explode(':', $input, 2);
        $this->response = $this->response->withAddedHeader(trim($key), trim($value));
    }

    /**
     * Add HTTP headers. The input array is all the header lines from the HTTP message. Optionally including the
     * status line.
     *
     * @param array $headers
     */
    public function parseHttpHeaders(array $headers): void
    {
        $statusLine = array_shift($headers);

        try {
            $this->setStatus($statusLine);
        } catch (InvalidArgumentException $e) {
            array_unshift($headers, $statusLine);
        }

        foreach ($headers as $header) {
            $this->addHeader($header);
        }
    }

    /**
     * Add some content to the body. This function writes the $input to a stream.
     *
     * @param string $input
     *
     * @return int returns the number of bytes written
     */
    public function writeBody(string $input): int
    {
        return $this->response->getBody()->write($input);
    }

    public function getResponse(): ResponseInterface
    {
        $this->response->getBody()->rewind();

        return $this->response;
    }
}
