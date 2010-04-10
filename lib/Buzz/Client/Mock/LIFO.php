<?php

namespace Buzz\Client\Mock;

class LIFO extends AbstractQueue
{
  public function __construct()
  {
    parent::__construct('array_push', 'array_pop');
  }
}
