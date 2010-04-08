<?php

namespace Buzz\Service\RightScale\Resource;

class RightScriptCollection extends RightScript implements \Iterator, \Countable
{
  protected $rightScripts = array();

  /**
   * @see AbstractResource
   */
  public function fromArray(array $array)
  {
    foreach ($array as $data)
    {
      $rightScript = new RightScript();
      $rightScript->fromArray($data);

      $this->addRightScript($rightScript);
    }
  }

  // rightscripts

  public function setRightScripts(array $rightScripts)
  {
    $this->rightScripts = array();
    $this->addRightScripts($rightScripts);
  }

  public function getRightScripts()
  {
    return $this->rightScripts;
  }

  public function addRightScripts($rightScripts)
  {
    foreach ($rightScripts as $rightScript)
    {
      $this->addRightScript($rightScript);
    }
  }

  public function addRightScript(RightScript $rightScript)
  {
    $this->rightScripts[] = $rightScript;
  }

  // Iterator

  public function key()
  {
    return key($this->rightScripts);
  }

  public function current()
  {
    return current($this->rightScripts);
  }

  public function next()
  {
    return next($this->rightScripts);
  }

  public function rewind()
  {
    return reset($this->rightScripts);
  }

  public function valid()
  {
    return false !== current($this->rightScripts);
  }

  // Countable

  public function count()
  {
    return count($this->rightScripts);
  }
}
