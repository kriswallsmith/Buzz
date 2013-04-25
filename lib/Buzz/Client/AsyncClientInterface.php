<?php

namespace Buzz\Client;

interface AsyncClientInterface extends BatchClientInterface
{
    /**
     * Processes the queued requests.
     */
    public function flush();

    /** Returns true if all deferred requests have completed */
    public function isDone();

    /** This will execute a tick, which may be a lot of work or none at all. You should keep calling until isDone() */
    public function proceed();

}
