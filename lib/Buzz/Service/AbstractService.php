<?php

namespace Buzz\Service;

abstract class AbstractService implements ServiceInterface
{
  protected $name;

  public function __construct()
  {
    $this->configure();

    if (!$this->getName())
    {
      $this->setName(get_class($this));
    }
  }

  protected function configure()
  {
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * @see ServiceInterface
   */
  public function getName()
  {
    return $this->name;
  }
}
