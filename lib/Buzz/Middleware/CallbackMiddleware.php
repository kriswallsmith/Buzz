<?php

namespace Buzz\Middleware;

use Buzz\Exception\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class CallbackMiddleware implements MiddlewareInterface
{
    private $callable;

    /**
     * The callback should expect either one or two arguments, depending on
     * whether it is receiving a pre or post send notification.
     *
     *     $listener = new CallbackListener(function($request, $response = null) {
     *         if ($response) {
     *             // postSend
     *         } else {
     *             // preSend
     *         }
     *     });
     *
     * @param mixed $callable A PHP callable
     *
     * @throws InvalidArgumentException If the argument is not callable
     */
    public function __construct($callable)
    {
        if (!is_callable($callable)) {
            throw new InvalidArgumentException('The argument is not callable.');
        }

        $this->callable = $callable;
    }

    public function handleRequest(RequestInterface $request, callable $next)
    {
        call_user_func($this->callable, $request);

        return $next($request);
    }

    public function handleResponse(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        call_user_func($this->callable, $request, $request);

        return $next($request, $response);
    }
}
