<?php

namespace Buzz\Service\RightScale\Resource;

class RightScript extends AbstractResource
{
  protected $name;
  protected $description;
  protected $script;
  protected $isHeadVersion;
  protected $version;
  protected $createdAt;
  protected $updatedAt;
  protected $href;

  /**
   * @see AbstractResource
   */
  public function fromArray(array $array)
  {
    $this->setCreatedAt(new \DateTime($array['created_at']));
    $this->setDescription($array['description']);
    $this->setIsHeadVersion($array['is_head_version']);
    $this->setName($array['name']);
    $this->setScript($array['script']);
    $this->setUpdatedAt(new \DateTime($array['updated_at']));
    $this->setVersion($array['version']);
    $this->setHref($array['href']);
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

  public function setCreatedAt(\DateTime $createdAt)
  {
    $this->createdAt = $createdAt;
  }

  public function getCreatedAt()
  {
    return $this->createdAt;
  }

  public function setUpdatedAt(\DateTime $updatedAt)
  {
    $this->updatedAt = $updatedAt;
  }

  public function getUpdatedAt()
  {
    return $this->updatedAt;
  }

  public function setHref($href)
  {
    $this->href = $href;
  }

  public function getHref()
  {
    return $this->href;
  }

  public function setIsHeadVersion($isHeadVersion)
  {
    $this->isHeadVersion = $isHeadVersion;
  }

  public function getIsHeadVersion()
  {
    return $this->isHeadVersion;
  }

  public function setVersion($version)
  {
    $this->version = $version;
  }

  public function getVersion()
  {
    return $this->version;
  }
}
