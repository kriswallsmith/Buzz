<?php

namespace Buzz\Browser;

use Buzz\Service;

class ServiceWrapper
{
  protected $browser;
  protected $service;

  public function __construct(Buzz\Browser $browser)
  {
    $this->setBrowser($browser);
  }

  public function setBrowser(Buzz\Browser $browser)
  {
    $this->browser = $browser;
  }

  public function getBrowser()
  {
    return $this->browser;
  }

  public function setService(Service\ServiceInterface $service)
  {
    $this->service = $service;
  }

  public function getService()
  {
    return $this->service;
  }

  /**
   * Terminates the fluent interface by returning to the browser.
   * 
   * @return Buzz\Browser The browser object
   */
  public function end()
  {
    return $this->getBrowser();
  }

  public function __call($method, $arguments)
  {
    if (!$service = $this->getService())
    {
      throw new LogicException('There is no service');
    }

    call_user_func_array(array($service, $method), $arguments);

    return $this;
  }
}
