<?php

namespace Buzz\Service;

use Buzz\Client;

abstract class AbstractService implements ServiceInterface
{
  protected $client;
  protected $name;

  /**
   * Constructor.
   * 
   * @param Client\ClientInterface $client A client object
   */
  public function __construct(Client\ClientInterface $client = null)
  {
    $this->setClient($client ?: new Client\FileGetContents());

    $this->configure();

    if (!$this->getName())
    {
      $this->setName(get_class($this));
    }
  }

  /**
   * A stub configuration method called in the constructor.
   */
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

  public function setClient(Client\ClientInterface $client)
  {
    $this->client = $client;
  }

  public function getClient()
  {
    return $this->client;
  }
}
