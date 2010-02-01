<?php

namespace Buzz\Service\RightScale;

/**
 * A collection of deployments.
 * 
 * Provides an interface for performing actions on multiple deployments.
 */
class DeploymentCollection extends Deployment implements \Iterator, \ArrayAccess, \Countable
{
  protected $deployments = array();

  /**
   * @see AbstractResource
   */
  public function fromArray(array $array)
  {
    foreach ($array as $data)
    {
      $deployment = new Deployment($this->getAPI());
      $deployment->fromArray($data);

      $this->addDeployment($deployment);
    }
  }

  /**
   * @throws LogicException This method is not allowed
   */
  public function setServers(ServerCollection $servers)
  {
    throw new LogicException('Cannot set a deployment collection\'s servers.');
  }

  /**
   * Returns all servers from the current deployment collection.
   * 
   * @return ServerCollection A collection of servers
   */
  public function getServers()
  {
    $servers = new ServerCollection($this->getAPI());

    foreach ($this->getDeployments() as $deployment)
    {
      $servers->addServers($deployment->getServers());
    }

    return $servers;
  }

  // deployments

  public function setDeployment($name, Deployment $deployment)
  {
    $this->deployments[$name] = $deployment;
  }

  public function setDeployments(array $deployments)
  {
    $this->deployments = $deployments;
  }

  public function getDeployment($name)
  {
    if (isset($this->deployments[$name]))
    {
      return $this->deployments[$name];
    }
  }

  public function getDeployments()
  {
    return $this->deployments;
  }

  public function hasDeployment($name)
  {
    return isset($this->deployments[$name]);
  }

  public function addDeployment(Deployment $deployment)
  {
    $this->deployments[] = $deployment;
  }

  public function addDeployments($deployments)
  {
    foreach ($deployments as $deployment)
    {
      $this->addDeployment($deployment);
    }
  }

  public function removeDeployment($name)
  {
    unset($this->deployments[$name]);
  }

  // ArrayAccess

  public function offsetSet($name, $value)
  {
    if (null === $name)
    {
      $this->addDeployment($value);
    }
    else
    {
      $this->setDeployment($name, $value);
    }
  }

  public function offsetGet($name)
  {
    return $this->getDeployment($name);
  }

  public function offsetExists($name)
  {
    return $this->hasDeployment($name);
  }

  public function offsetUnset($name)
  {
    $this->removeDeployment($name);
  }

  // Iterator

  public function key()
  {
    return key($this->deployments);
  }

  public function current()
  {
    return current($this->deployments);
  }

  public function next()
  {
    return next($this->deployments);
  }

  public function rewind()
  {
    return reset($this->deployments);
  }

  public function valid()
  {
    return false !== current($this->deployments);
  }

  // Countable

  public function count()
  {
    return count($this->deployments);
  }
}
