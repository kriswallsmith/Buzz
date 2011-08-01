<?php

namespace Buzz\Client\Mock;

use Buzz\Message;

class BatchFIFO extends AsyncBatchAbstractQueue
{
    private $queueCounter = 0;

    public function receiveFromQueue()
    {
        if (count($this->queue)) {
            $this->queueCounter = 0;
            return array_pop($this->queue);
        }
    }

    public function getFromQueue()
    {
        if (isset($this->queue[0][$this->queueCounter])) {
            return $this->queue[0][$this->queueCounter];
        }
    }

    public function updateInQueue($queued)
    {
        if (isset($this->queue[0][$this->queueCounter])) {
            $this->queue[0][$this->queueCounter] = $queued;
            ++$this->queueCounter;
        }
    }
}
