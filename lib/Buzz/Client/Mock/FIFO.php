<?php

namespace Buzz\Client\Mock;

class FIFO extends AbstractQueue
{
    public function __construct()
    {
        parent::__construct('array_push', 'array_shift');
    }
}
