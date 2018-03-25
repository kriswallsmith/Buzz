<?php

declare(strict_types=1);

namespace Buzz\Message;

use Buzz\Converter\HeaderConverter;
use Buzz\Exception\ClientException;
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
     * @var HTTPlugResponseFactory|InteropResponseFactory
     */
    private $responseFactory;

    /**
     * @var null|resource
     */
    private $stream = null;

    /**
     * @var null|string
     */
    private $body = null;

    private $protocolVersion;
    private $statusCode;
    private $reasonPhrase;
    private $headers = [];

    /**
     * @param HTTPlugResponseFactory|InteropResponseFactory $responseFactory
     */
    public function __construct($responseFactory)
    {
        if (!$responseFactory instanceof HTTPlugResponseFactory) {
            throw new InvalidArgumentException('First parameter to ResponseBuilder must be a response factory');
        }

        $this->responseFactory = $responseFactory;
    }

    public function setStatus(string $input): void
    {
        $parts = explode(' ', $input, 3);
        if (count($parts) < 2 || strpos(strtolower($parts[0]), 'http/') !== 0) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid HTTP status line', $input));
        }

        $this->protocolVersion = (string) substr($parts[0], 5);
        $this->statusCode = (int) $parts[1];
        $this->reasonPhrase = isset($parts[2]) ? $parts[2] : '';
    }

    /**
     * Add a single HTTP header line.
     * @param string $input
     */
    public function addHeader(string $input): void
    {
        $this->headers[] = $input;
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
        if ($this->stream === null) {
            if (false === $this->stream = fopen('php://temp', 'w+b')) {
                throw new ClientException('Could not open stream');
            }
        }

        if ($this->body !== null) {
            throw new InvalidArgumentException('You cannot use both writeBody and setBody');
        }

        return fwrite($this->stream, $input);
    }

    /**
     * Replace the body with $input. This function should be used for smaller bodies only.
     *
     * @param string $input
     */
    public function setBody(string $input): void
    {
        if ($this->stream !== null) {
            throw new InvalidArgumentException('You cannot use both writeBody and setBody');
        }

        $this->body = $input;
    }

    public function getResponse(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(
            $this->statusCode,
            $this->reasonPhrase,
            HeaderConverter::toPsrHeaders($this->headers),
            null === $this->stream ? $this->body : $this->stream,
            $this->protocolVersion
        );

        $response->getBody()->rewind();

        return $response;
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
}
