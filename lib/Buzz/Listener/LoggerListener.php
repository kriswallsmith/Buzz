<?php

namespace Buzz\Listener;

use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;
use Buzz\Exception\InvalidArgumentException;

class LoggerListener implements ListenerInterface
{
    private $logger;
    private $prefix;
    private $startTime;

    /**
     * @param Callable    $logger Some logger callable
     * @param string|null $prefix The logging prefix
     *
     * @throws InvalidArgumentException
     */
    public function __construct($logger, $prefix = null)
    {
        if (!is_callable($logger)) {
            throw new InvalidArgumentException('The logger must be a callable.');
        }

        $this->logger = $logger;
        $this->prefix = $prefix;
    }

    /**
     * {@inheritDoc}
     */
    public function preSend(RequestInterface $request)
    {
        $this->startTime = microtime(true);
    }

    /**
     * {@inheritDoc}
     */
    public function postSend(RequestInterface $request, MessageInterface $response)
    {
        $seconds = microtime(true) - $this->startTime;

        call_user_func($this->logger, sprintf('%sSent "%s %s%s" in %dms', $this->prefix, $request->getMethod(), $request->getHost(), $request->getResource(), round($seconds * 1000)));
    }
}
