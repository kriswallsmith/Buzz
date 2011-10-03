<?php

namespace Buzz\Browser;

use Buzz\Browser;

use Buzz\Client;
use Buzz\History;
use Buzz\Message;
use Buzz\BrowserAwareInterface;

/**
 * Browser for profiling requests. Stores the time to send and receive
 * the request inside the Journal entries.
 *
 * @see Buzz\History\Entry
 */
class ProfilingBrowser extends Browser
{
    /**
     * {@inheritdoc}
     */
    public function send(Message\Request $request, Message\Response $response = null)
    {
        if (null === $response) {
            $response = $this->createResponse();
        }

        if ($request instanceof BrowserAwareInterface) {
            $request->setBrowser($this);
        }

        $start = microtime(true);

        $this->getClient()->send($request, $response);

        $end = microtime(true);
        $time = ($end - $start) * 1000;

        $this->getJournal()->record($request, $response, (int) $time);

        return $response;
    }
}
