<?php

namespace Buzz\Listener;

use Buzz\History;
use Buzz\Message;

class HistoryListener implements ListenerInterface
{
    private $journal;

    public function __construct(History\Journal $journal = null)
    {
        $this->journal = $journal ?: new History\Journal();
    }

    public function preSend(Message\Request $request)
    {
    }

    public function postSend(Message\Request $request, Message\Response $response)
    {
        $this->journal->record($request, $response);
    }
}
