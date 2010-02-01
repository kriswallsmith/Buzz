<?php

namespace Buzz\Service\RightScale;

class RightScript extends AbstractResource
{
  protected $name;
  protected $description;
  protected $script;

  /**
   * @see AbstractResource
   */
  public function fromArray(array $array)
  {
    $this->setName($array['name']);
    $this->setDescription($array['description']);
    $this->setCreatedAt(new \DateTime($array['created_at']));
    $this->setUpdatedAt(new \DateTime($array['updated_at']));
    $this->setScript($array['script']);
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function getName()
  {
    return $this->name;
  }

  public function setDescription($description)
  {
    $this->description = $description;
  }

  public function getDescription()
  {
    return $this->description;
  }

  public function setScript($script)
  {
    $this->script = $script;
  }

  public function getScript()
  {
    return $this->script;
  }
}
