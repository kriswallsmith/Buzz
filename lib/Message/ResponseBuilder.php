<?php

declare(strict_types=1);

namespace Buzz\Message;

use Buzz\Exception\InvalidArgumentException;
use Http\Message\ResponseFactory as HTTPlugResponseFactory;
use Psr\Http\Message\ResponseFactoryInterface as PsrResponseFactory;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class ResponseBuilder
{
    /**
     * @var ResponseInterface
     */
    private $response;
    private $responseFactory;

    /**
     * @param HTTPlugResponseFactory|PsrResponseFactory $responseFactory
     */
    public function __construct($responseFactory)
    {
        if (!$responseFactory instanceof HTTPlugResponseFactory && !$responseFactory instanceof PsrResponseFactory) {
            throw new InvalidArgumentException('First parameter to ResponseBuilder must be a response factory');
        }

        $this->responseFactory = $responseFactory;
        $this->response = $responseFactory->createResponse();
    }

    public function getResponseFromRawInput(string $raw, int $headerSize): ResponseInterface
    {
        $headers = substr($raw, 0, $headerSize);
        $this->parseHttpHeaders(explode("\n", $headers));
        $this->writeBody(substr($raw, $headerSize));

        return $this->getResponse();
    }

    private function filterHeaders(array $headers): array
    {
        $filtered = [];
        foreach ($headers as $header) {
            if (0 === stripos($header, 'http/')) {
                $filtered = [];
                $filtered[] = trim($header);
                continue;
            }

            // Make sure they are not empty
            $trimmed = trim($header);
            if (false === strpos($trimmed, ':')) {
                continue;
            }

            $filtered[] = $trimmed;
        }

        return $filtered;
    }

    public function setStatus(string $input): void
    {
        $parts = explode(' ', $input, 3);
        if (\count($parts) < 2 || 0 !== strpos(strtolower($parts[0]), 'http/')) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid HTTP status line', $input));
        }

        $this->response = $this->response->withStatus((int) $parts[1], isset($parts[2]) ? $parts[2] : '');
        $this->response = $this->response->withProtocolVersion((string) substr($parts[0], 5));
    }

    /**
     * Add a single HTTP header line.
     */
    public function addHeader(string $input): void
    {
        list($key, $value) = explode(':', $input, 2);
        $this->response = $this->response->withAddedHeader(trim($key), trim($value));
    }

    /**
     * Add HTTP headers. The input array is all the header lines from the HTTP message. Optionally including the
     * status line.
     */
    public function parseHttpHeaders(array $headers): void
    {
        $headers = $this->filterHeaders($headers);
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
     * @return int returns the number of bytes written
     */
    public function writeBody(string $input): int
    {
        return $this->response->getBody()->write($input);
    }

    public function getResponse(): ResponseInterface
    {
        $this->response->getBody()->rewind();
        $response = $this->response;
        $this->response = $this->responseFactory->createResponse();

        return $response;
    }
}
