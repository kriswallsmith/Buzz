<?php

namespace Buzz\Service\RightScale\Resource;

class Deployment extends AbstractResource
{
  protected $nickname;
  protected $description;
  protected $href;
  protected $createdAt;
  protected $updatedAt;

  /**
   * @var TagCollection
   */
  protected $tags;

  /**
   * @var ServerCollection
   */
  protected $servers;

  /**
   * @see AbstractResource
   */
  public function fromArray(array $array)
  {
    $this->setNickname($array['nickname']);
    $this->setDescription($array['description']);
    $this->setHref($array['href']);
    $this->setCreatedAt(new \DateTime($array['created_at']));
    $this->setUpdatedAt(new \DateTime($array['updated_at']));

    $tags = new TagCollection();
    $tags->fromArray($array['tags']);
    $this->setTags($tags);

    $servers = new ServerCollection();
    $servers->fromArray($array['servers']);
    $this->setServers($servers);
  }

  /**
   * Finds servers by nickname.
   * 
   * @param string $nickname A nickname or regular expression
   * 
   * @return ServerCollection A collection of matching servers
   */
  public function findServersByNickname($nickname, $limit = null)
  {
    $servers = new ServerCollection();

    // choose a comparision function
    if (preg_match('/^(!)?([^a-zA-Z0-9\\\\]).+?\\2[ims]?$/', $nickname, $match))
    {
      if ('!' == $match[1])
      {
        $compare = function ($nickname, $value) { return !preg_match(substr($nickname, 1), $value); };
      }
      else
      {
        $compare = function ($nickname, $value) { return preg_match($nickname, $value); };
      }
    }
    else
    {
      $compare = function ($nickname, $value) { return $nickname == $value; };
    }

    foreach ($this->getServers() as $server)
    {
      if (null !== $limit && count($servers) >= $limit)
      {
        break;
      }

      if ($compare($nickname, $server->getNickname()))
      {
        $servers->addServer($server);
      }
    }

    return $servers;
  }

  /**
   * Finds a single server by nickname.
   * 
   * @param string $nickname A nickname or regular expression
   * 
   * @return Server|null The server, if found
   */
  public function findServerByNickname($nickname)
  {
    $servers = $this->findServersByNickname($nickname, 1);

    return count($servers) ? $servers->getServer(0) : null;
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

  public function setDescription($description)
  {
    $this->description = $description;
  }

  public function getDescription()
  {
    return $this->description;
  }

  public function setTags(TagCollection $tags)
  {
    $this->tags = $tags;
  }

  public function getTags()
  {
    return $this->tags;
  }

  public function setServers(ServerCollection $servers)
  {
    $this->servers = $servers;
  }

  public function getServers()
  {
    return $this->servers;
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
