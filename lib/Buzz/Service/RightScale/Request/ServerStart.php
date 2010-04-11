<?php

namespace Buzz\Service\RightScale\Request;

class ServerStart extends AbstractServerRequest
{
  public function __construct($serverId)
  {
    parent::__construct($serverId, 'start');
  }
}
