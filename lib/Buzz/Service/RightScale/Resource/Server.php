<?php

namespace Buzz\Service\RightScale\Resource;

use Buzz\Message;

class Server extends AbstractResource
{
  protected $nickname;
  protected $serverType;
  protected $state;
  protected $deploymentHref;
  protected $currentInstanceHref;
  protected $href;
  protected $createdAt;
  protected $updatedAt;

  /**
   * @var TagCollection
   */
  protected $tags;

  /**
   * @see AbstractResource
   */
  public function fromArray(array $array)
  {
    $this->setCreatedAt(new \DateTime($array['created_at']));
    $this->setUpdatedAt(new \DateTime($array['updated_at']));
    $this->setNickname($array['nickname']);
    $this->setServerType($array['server_type']);
    $this->setHref($array['href']);
    $this->setState($array['state']);
    $this->setDeploymentHref($array['deployment_href']);
    $this->setCurrentInstanceHref($array['current_instance_href']);

    $tags = new TagCollection();
    $tags->fromArray($array['tags']);
    $this->setTags($tags);
  }

  // accessors and mutators

  public function setNickname($nickname)
  {
    $this->nickname = $nickname;
  }

  public function getNickname()
  {
    return $this->nickname;
  }

  public function setServerType($serverType)
  {
    $this->serverType = $serverType;
  }

  public function getServerType()
  {
    return $this->serverType;
  }

  public function setState($state)
  {
    $this->state = $state;
  }

  public function getState()
  {
    return $this->state;
  }

  public function setDeploymentHref($deploymentHref)
  {
    $this->deploymentHref = $deploymentHref;
  }

  public function getDeploymentHref()
  {
    return $this->deploymentHref;
  }

  public function setCurrentInstanceHref($currentInstanceHref)
  {
    $this->currentInstanceHref = $currentInstanceHref;
  }

  public function getCurrentInstanceHref()
  {
    return $this->currentInstanceHref;
  }

  public function setTags(TagCollection $tags)
  {
    $this->tags = $tags;
  }

  public function getTags()
  {
    return $this->tags;
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
}
