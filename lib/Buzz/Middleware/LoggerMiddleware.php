<?php

namespace Buzz\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class LoggerMiddleware implements MiddlewareInterface
{
    private $logger;
    private $level;
    private $prefix;
    private $startTime;

    /**
     *
     * @param LoggerInterface $logger
     * @param string $level
     * @param string|null $prefix
     */
    public function __construct(LoggerInterface $logger, $level = 'info', $prefix = null)
    {
        $this->logger = $logger;
        $this->level = $level;
        $this->prefix = $prefix;
    }

    public function handleRequest(\Psr\Http\Message\RequestInterface $request, callable $next)
    {
        $this->startTime = microtime(true);

        return $next($request);
    }

    public function handleResponse(\Psr\Http\Message\RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $seconds = microtime(true) - $this->startTime;
        $this->logger->log($this->level, sprintf('%sSent "%s %s" in %dms', $this->prefix, $request->getMethod(), $request->getUri(), round($seconds * 1000)));

        return $next($request, $response);
    }
}
