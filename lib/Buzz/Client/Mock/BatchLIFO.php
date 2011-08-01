<?php

namespace Buzz\Client\Mock;

use Buzz\Message;

class BatchLIFO extends AsyncBatchAbstractQueue
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
        $index = count($this->queue);
        if ($index && isset($this->queue[$index-1][$this->queueCounter])) {
            return $this->queue[$index-1][$this->queueCounter];
        }
    }

    public function updateInQueue($queued)
    {
        $index = count($this->queue);
        if ($index && isset($this->queue[$index-1][$this->queueCounter])) {
            $this->queue[$index-1][$this->queueCounter] = $queued;
            ++$this->queueCounter;
        }
    }
}
