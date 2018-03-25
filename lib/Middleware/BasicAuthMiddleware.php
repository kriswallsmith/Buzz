<?php

declare(strict_types=1);

namespace Buzz\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class BasicAuthMiddleware implements MiddlewareInterface
{
    private $username;

    private $password;

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function handleRequest(RequestInterface $request, callable $next)
    {
        $request = $request->withAddedHeader('Authorization', sprintf('Basic %s', base64_encode($this->username.':'.$this->password)));

        return $next($request);
    }

    public function handleResponse(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        return $next($request, $response);
    }
}
