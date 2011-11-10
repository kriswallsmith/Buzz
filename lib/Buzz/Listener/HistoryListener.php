<?php

namespace Buzz\Listener;

use Buzz\History;
use Buzz\Message;

class HistoryListener implements ListenerInterface
{
    private $journal;
    private $startTime;

    public function __construct(History\Journal $journal)
    {
        $this->journal = $journal;
    }

    public function getJournal()
    {
        return $this->journal;
    }

    public function preSend(Message\Request $request)
    {
        $this->startTime = microtime(true);
    }

    public function postSend(Message\Request $request, Message\Response $response)
    {
        $this->journal->record($request, $response, microtime(true) - $this->startTime);
    }
}
