<?php

declare(strict_types=1);

namespace Buzz\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ContentLengthMiddleware implements MiddlewareInterface
{
    public function handleRequest(RequestInterface $request, callable $next)
    {
        $body = $request->getBody();

        if (!$request->hasHeader('Content-Length')) {
            $request = $request->withAddedHeader('Content-Length', (string) $body->getSize());
        }

        return $next($request);
    }

    public function handleResponse(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        return $next($request, $response);
    }
}
