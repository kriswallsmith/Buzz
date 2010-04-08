<?php

namespace Buzz\Service\RightScale\Resource;

/**
 * A collection of servers.
 * 
 * Provides an interface for performing actions on multiple servers.
 */
class ServerCollection extends Server implements \Iterator, \Countable
{
  protected $servers = array();

  /**
   * @see AbstractResource
   */
  public function fromArray(array $array)
  {
    foreach ($array as $data)
    {
      $server = new Server();
      $server->fromArray($data);

      $this->addServer($server);
    }
  }

  // servers

  public function setServers(array $servers)
  {
    $this->servers = array();
    $this->addServers($servers);
  }

  public function getServers()
  {
    return $this->servers;
  }

  public function addServers($servers)
  {
    foreach ($servers as $server)
    {
      $this->addServer($server);
    }
  }

  public function addServer(Server $server)
  {
    $this->servers[] = $server;
  }

  // Iterator

  public function key()
  {
    return key($this->servers);
  }

  public function current()
  {
    return current($this->servers);
  }

  public function next()
  {
    return next($this->servers);
  }

  public function rewind()
  {
    return reset($this->servers);
  }

  public function valid()
  {
    return false !== current($this->servers);
  }

  // Countable

  public function count()
  {
    return count($this->servers);
  }
}
