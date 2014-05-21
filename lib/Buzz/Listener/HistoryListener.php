<?php

namespace Buzz\Listener;

use Buzz\Listener\History\Journal;
use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;

class HistoryListener implements ListenerInterface
{
    private $journal;
    private $startTime;

    /**
     * @param Journal $journal
     */
    public function __construct(Journal $journal)
    {
        $this->journal = $journal;
    }

    /**
     * @return Journal
     */
    public function getJournal()
    {
        return $this->journal;
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
        $this->journal->record($request, $response, microtime(true) - $this->startTime);
    }
}
