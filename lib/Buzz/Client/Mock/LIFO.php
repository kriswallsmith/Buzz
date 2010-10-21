<?php

namespace Buzz\Client\Mock;

class LIFO extends AbstractQueue
{
    public function receiveFromQueue()
    {
        if (count($this->queue)) {
            return array_pop($this->queue);
        }
    }
}
