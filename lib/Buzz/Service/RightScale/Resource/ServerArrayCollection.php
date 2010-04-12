<?php

namespace Buzz\Service\RightScale\Resource;

class ServerArrayCollection extends ServerArray implements \Iterator, \Countable
{
  protected $serverArrays = array();

  /**
   * @see AbstractResource
   */
  public function fromArray(array $array)
  {
    foreach ($array as $data)
    {
      $serverArray = new ServerArray();
      $serverArray->fromArray($data);

      $this->addServerArray($serverArray);
    }
  }

  // server arrays

  public function setServerArrays(array $serverArrays)
  {
    $this->serverArrays = array();
    $this->addServerArrays($serverArrays);
  }

  public function getServerArrays()
  {
    return $this->serverArrays;
  }

  public function addServerArrays($serverArrays)
  {
    foreach ($serverArrays as $serverArray)
    {
      $this->addServerArray($serverArray);
    }
  }

  public function addServerArray(ServerArray $serverArray)
  {
    $this->serverArrays[] = $serverArray;
  }

  // Iterator

  public function key()
  {
    return key($this->serverArrays);
  }

  public function current()
  {
    return current($this->serverArrays);
  }

  public function next()
  {
    return next($this->serverArrays);
  }

  public function rewind()
  {
    return reset($this->serverArrays);
  }

  public function valid()
  {
    return false !== current($this->serverArrays);
  }

  // Countable

  public function count()
  {
    return count($this->serverArrays);
  }
}
