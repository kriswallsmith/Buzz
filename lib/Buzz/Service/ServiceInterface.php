<?php

namespace Buzz\Service;

interface ServiceInterface
{
  /**
   * Returns the current service's default name.
   * 
   * @return string
   */
  public function getName();
}
