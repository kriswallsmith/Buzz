<?php

namespace Buzz\Service\RightScale\Request;

use Buzz\Message;
use Buzz\Service\RightScale\Resource;

abstract class ServerRequest extends Message\Request
{
  protected $serverId;

  public function __construct($serverId, $action)
  {
    $this->setServerId($serverId);

    parent::__construct(static::METHOD_POST, '/api/acct/%s/servers/'.$this->getServerId().'/'.$action);
  }

  public function setServerId($serverId)
  {
    if ($serverId instanceof Resource\Server)
    {
      $serverId = $serverId->getId();
    }

    $this->serverId = $serverId;
  }

  public function getServerId()
  {
    return $this->serverId;
  }
}
