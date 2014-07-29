<?php

namespace Buzz\Client;

/**
 * A client capable of running batches of requests.
 *
 * The Countable implementation should return the number of queued requests.
 */
interface BatchClientInterface extends ClientInterface, \Countable
{
    /**
     * Processes all queued requests.
     */
    public function flush();

    /**
     * Processes zero or more queued requests.
     */
    public function proceed();
}
