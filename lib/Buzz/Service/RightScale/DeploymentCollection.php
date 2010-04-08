<?php

namespace Buzz\Service\RightScale;

/**
 * A collection of deployments.
 * 
 * Provides an interface for performing actions on multiple deployments.
 */
class DeploymentCollection extends Deployment implements \Iterator, \Countable
{
  protected $deployments = array();

  /**
   * @see AbstractResource
   */
  public function fromArray(array $array)
  {
    foreach ($array as $data)
    {
      $deployment = new Deployment();
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
    $servers = new ServerCollection();

    foreach ($this->getDeployments() as $deployment)
    {
      $servers->addServers($deployment->getServers());
    }

    return $servers;
  }

  // deployments

  public function setDeployments(array $deployments)
  {
    $this->deployments = array();
    $this->addDeployments($deployments);
  }

  public function getDeployments()
  {
    return $this->deployments;
  }

  public function addDeployments($deployments)
  {
    foreach ($deployments as $deployment)
    {
      $this->addDeployment($deployment);
    }
  }

  public function addDeployment(Deployment $deployment)
  {
    $this->deployments[] = $deployment;
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
