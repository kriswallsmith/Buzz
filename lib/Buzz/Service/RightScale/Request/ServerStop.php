<?php

namespace Buzz\Service\RightScale\Request;

class ServerStart extends ServerRequest
{
  public function __construct($serverId)
  {
    parent::__construct($serverId, 'stop');
  }
}
