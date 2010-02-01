<?php

namespace Buzz\Service\RightScale;

/**
 * A collection of servers.
 * 
 * Provides an interface for performing actions on multiple servers.
 */
class ServerCollection extends Server implements \Iterator, \ArrayAccess, \Countable
{
  protected $servers = array();

  /**
   * @see AbstractResource
   */
  public function fromArray(array $array)
  {
    foreach ($array as $data)
    {
      $server = new Server($this->getAPI());
      $server->fromArray($data);

      $this->addServer($server);
    }
  }

  // servers

  public function setServer($name, Server $server)
  {
    $this->servers[$name] = $server;
  }

  public function setServers(array $servers)
  {
    $this->servers = $servers;
  }

  public function getServer($name)
  {
    if (isset($this->servers[$name]))
    {
      return $this->servers[$name];
    }
  }

  public function getServers()
  {
    return $this->servers;
  }

  public function hasServer($name)
  {
    return isset($this->servers[$name]);
  }

  public function addServer(Server $server)
  {
    $this->servers[] = $server;
  }

  public function addServers($servers)
  {
    foreach ($servers as $server)
    {
      $this->addServer($server);
    }
  }

  public function removeServer($name)
  {
    unset($this->servers[$name]);
  }

  // ArrayAccess

  public function offsetSet($name, $value)
  {
    if (null === $name)
    {
      $this->addServer($value);
    }
    else
    {
      $this->setServer($name, $value);
    }
  }

  public function offsetGet($name)
  {
    return $this->getServer($name);
  }

  public function offsetExists($name)
  {
    return $this->hasServer($name);
  }

  public function offsetUnset($name)
  {
    $this->removeServer($name);
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
