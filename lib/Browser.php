<?php

declare(strict_types=1);

namespace Buzz;

use Buzz\Client\BuzzClientInterface;
use Buzz\Client\FileGetContents;
use Buzz\Exception\ClientException;
use Buzz\Exception\InvalidArgumentException;
use Buzz\Exception\LogicException;
use Buzz\Middleware\MiddlewareInterface;
use Http\Message\RequestFactory;
use Interop\Http\Factory\RequestFactoryInterface;
use Nyholm\Psr7\Factory\MessageFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Browser implements BuzzClientInterface
{
    /** @var BuzzClientInterface */
    private $client;

    /** @var RequestFactoryInterface|RequestFactory */
    private $factory;

    /**
     * @var MiddlewareInterface[]
     */
    private $middlewares = [];

    /** @var RequestInterface */
    private $lastRequest;

    /** @var ResponseInterface */
    private $lastResponse;

    public function __construct(BuzzClientInterface $client = null)
    {
        $this->client = $client ?: new FileGetContents();
        $this->factory = new MessageFactory();
    }

    public function get(string $url, array $headers = []): ResponseInterface
    {
        return $this->request('GET', $url, $headers);
    }

    public function post(string $url, array $headers = [], string $body = ''): ResponseInterface
    {
        return $this->request('POST', $url, $headers, $body);
    }

    public function head(string $url, array  $headers = []): ResponseInterface
    {
        return $this->request('HEAD', $url, $headers);
    }

    public function patch(string $url, array  $headers = [], string $body = ''): ResponseInterface
    {
        return $this->request('PATCH', $url, $headers, $body);
    }

    public function put(string $url, array  $headers = [], string $body = ''): ResponseInterface
    {
        return $this->request('PUT', $url, $headers, $body);
    }

    public function delete(string $url, array  $headers = [], string $body = ''): ResponseInterface
    {
        return $this->request('DELETE', $url, $headers, $body);
    }

    /**
     * Sends a request.
     *
     * @param string $method  The request method to use
     * @param string $url     The URL to call
     * @param array  $headers An array of request headers
     * @param string $body    The request content
     *
     * @return ResponseInterface The response object
     */
    public function request(string $method, string $url, array $headers = [], string $body = ''): ResponseInterface
    {
        $request = $this->createRequest($method, $url, $headers, $body);

        return $this->sendRequest($request);
    }

    /**
     * Submit a form.
     *
     * @throws ClientException
     * @throws LogicException
     * @throws InvalidArgumentException
     */
    public function submitForm(string $url, array $fields, string $method = 'POST', array $headers = []): ResponseInterface
    {
        $body = [];
        $files = '';
        $boundary = uniqid('', true);
        foreach ($fields as $name => $field) {
            if (!isset($field['path'])) {
                $body[$name] = $field;
            } else {
                // This is a file
                $fileContent = file_get_contents($field['path']);
                $files .= $this->prepareMultipart($name, $fileContent, $boundary, $field);
            }
        }

        if (empty($files)) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
            $body = http_build_query($body);
        } else {
            $headers['Content-Type'] = 'multipart/form-data; boundary="'.$boundary.'"';

            foreach ($body as $name => $value) {
                $files .= $this->prepareMultipart($name, $value, $boundary);
            }
            $body = "$files--{$boundary}--\r\n";
        }

        $request = $this->createRequest($method, $url, $headers, $body);

        return $this->sendRequest($request);
    }

    /**
     * Send a PSR7 request.
     *
     * @throws ClientException
     * @throws LogicException
     * @throws InvalidArgumentException
     */
    public function sendRequest(RequestInterface $request, array $options = []): ResponseInterface
    {
        $chain = $this->createMiddlewareChain($this->middlewares, function (RequestInterface $request, callable $responseChain) use ($options) {
            $response = $this->client->sendRequest($request, $options);
            $responseChain($request, $response);
        }, function (RequestInterface $request, ResponseInterface $response) {
            $this->lastRequest = $request;
            $this->lastResponse = $response;
        });

        // Call the chain
        $chain($request);

        return $this->lastResponse;
    }

    /**
     * @param MiddlewareInterface[] $middlewares
     * @param callable              $requestChainLast
     * @param callable              $responseChainLast
     *
     * @return callable
     */
    private function createMiddlewareChain(array $middlewares, callable $requestChainLast, callable $responseChainLast): callable
    {
        $responseChainNext = $responseChainLast;

        // Build response chain
        /** @var MiddlewareInterface $middleware */
        foreach ($middlewares as $middleware) {
            $lastCallable = function (RequestInterface $request, ResponseInterface $response) use ($middleware, $responseChainNext) {
                return $middleware->handleResponse($request, $response, $responseChainNext);
            };

            $responseChainNext = $lastCallable;
        }

        $requestChainLast = function (RequestInterface $request) use ($requestChainLast, $responseChainNext) {
            // Send the actual request and get the response
            $requestChainLast($request, $responseChainNext);
        };

        $middlewares = array_reverse($middlewares);

        // Build request chain
        $requestChainNext = $requestChainLast;
        /** @var MiddlewareInterface $middleware */
        foreach ($middlewares as $middleware) {
            $lastCallable = function (RequestInterface $request) use ($middleware, $requestChainNext) {
                return $middleware->handleRequest($request, $requestChainNext);
            };

            $requestChainNext = $lastCallable;
        }

        return $requestChainNext;
    }

    public function getLastRequest(): ?RequestInterface
    {
        return $this->lastRequest;
    }

    public function getLastResponse(): ?ResponseInterface
    {
        return $this->lastResponse;
    }

    public function getClient(): BuzzClientInterface
    {
        return $this->client;
    }

    /**
     * Add a new middleware to the stack.
     *
     * @param MiddlewareInterface $middleware
     */
    public function addMiddleware(MiddlewareInterface $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    private function prepareMultipart(string $name, string $content, string $boundary, array $data = []): string
    {
        $output = '';
        $fileHeaders = [];

        // Set a default content-disposition header
        $fileHeaders['Content-Disposition'] = sprintf('form-data; name="%s"', $name);
        if (isset($data['filename'])) {
            $fileHeaders['Content-Disposition'] .= sprintf('; filename="%s"', $data['filename']);
        }

        // Set a default content-length header
        if ($length = strlen($content)) {
            $fileHeaders['Content-Length'] = (string) $length;
        }

        if (isset($data['contentType'])) {
            $fileHeaders['Content-Type'] = $data['contentType'];
        }

        // Add start
        $output .= "--$boundary\r\n";
        foreach ($fileHeaders as $key => $value) {
            $output .= sprintf("%s: %s\r\n", $key, $value);
        }
        $output .= "\r\n";
        $output .= $content;
        $output .= "\r\n";

        return $output;
    }

    protected function createRequest(string $method, string $url, array $headers, $body): RequestInterface
    {
        $request = $this->factory->createRequest($method, $url);
        $request->getBody()->write($body);
        foreach ($headers as $name => $value) {
            $request = $request->withAddedHeader($name, $value);
        }

        return $request;
    }
}
