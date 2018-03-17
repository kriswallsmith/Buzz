<?php
declare(strict_types=1);

namespace Buzz\Client;

use Buzz\Exception\ClientException;
use Psr\Http\Client\ClientInterface;

/**
 * A client capable of running batches of requests.
 *
 * The Countable implementation should return the number of queued requests.
 */
interface BatchClientInterface extends \Countable
{
    /**
     * Processes all queued requests.
     *
     * @throws ClientException If something goes wrong
     */
    public function flush();

    /**
     * Processes zero or more queued requests.
     *
     * @throws ClientException If something goes wrong
     */
    public function proceed();
}
