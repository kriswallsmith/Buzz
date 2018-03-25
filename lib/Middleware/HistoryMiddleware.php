<?php

declare(strict_types=1);

namespace Buzz\Middleware;

use Buzz\Middleware\History\Journal;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HistoryMiddleware implements MiddlewareInterface
{
    private $journal;

    private $startTime;

    public function __construct(Journal $journal)
    {
        $this->journal = $journal;
    }

    public function getJournal(): Journal
    {
        return $this->journal;
    }

    public function handleRequest(RequestInterface $request, callable $next)
    {
        $this->startTime = microtime(true);

        return $next($request);
    }

    public function handleResponse(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $this->journal->record($request, $response, microtime(true) - $this->startTime);

        return $next($request, $response);
    }
}
