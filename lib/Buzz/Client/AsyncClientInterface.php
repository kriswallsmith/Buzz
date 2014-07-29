<?php

namespace Buzz\Client;

interface AsyncClientInterface extends BatchClientInterface
{
    /**
     * Returns true if all deferred requests have completed
     */
    public function isDone();

    /**
     * Returns the number of requests currently in process.
     */
    public function queueSize();

    /**
     * This will execute a tick, which may be a lot of work or none at all. You should keep calling until isDone()
     */
    public function proceed();

}
