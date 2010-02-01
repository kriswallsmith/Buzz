<?php

namespace Buzz\Service\RightScale;

class Deployment extends AbstractResource
{
  protected $nickname;
  protected $description;

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
    $this->setCreatedAt(new \DateTime($array['created_at']));
    $this->setUpdatedAt(new \DateTime($array['updated_at']));
    $this->setNickname($array['nickname']);
    $this->setHref($array['href']);
    $this->setDescription($array['description']);

    $tags = new TagCollection($this->getAPI());
    $tags->fromArray($array['tags']);
    $this->setTags($tags);

    $servers = new ServerCollection($this->getAPI());
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
    $servers = new ServerCollection($this->getAPI());

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

  // actions

  /**
   * Starts all of the current deployment's servers.
   * 
   * @return Message\Response The API response
   */
  public function startAll()
  {
    $request = new Message\Request('POST');
    $request->fromUrl($this->getHref().'/start_all');

    $response = new Message\Response();

    $this->getAPI()->send($request, $response);

    return $response;
  }

  /**
   * Stops all of the current deployment's servers.
   * 
   * @return Message\Response The API response
   */
  public function stopAll()
  {
    $request = new Message\Request('POST');
    $request->fromUrl($this->getHref().'/stop_all');

    $response = new Message\Response();

    $this->getAPI()->send($request, $response);

    return $response;
  }

  /**
   * Duplicates the current deployment.
   * 
   * @return Message\Response The API response
   */
  public function duplicate()
  {
    $request = new Message\Request('POST');
    $request->fromUrl($this->getHref().'/duplicate');

    $response = new Message\Response();

    $this->getAPI()->send($request, $response);

    return $response;
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
}
