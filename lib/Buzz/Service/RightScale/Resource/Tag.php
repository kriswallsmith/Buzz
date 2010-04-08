<?php

namespace Buzz\Service\RightScale\Resource;

class Tag extends AbstractResource
{
  protected $name;

  /**
   * @see AbstractResource
   */
  public function fromArray(array $array)
  {
    $this->setName($array['name']);
  }

  // accessors and mutators

  public function setName($name)
  {
    $this->name = $name;
  }

  public function getName()
  {
    return $this->name;
  }
}
