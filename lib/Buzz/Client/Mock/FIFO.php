<?php

namespace Buzz\Client\Mock;

class FIFO extends AbstractQueue
{
    public function receiveFromQueue()
    {
        if (count($this->queue)) {
            return array_shift($this->queue);
        }
    }
}
