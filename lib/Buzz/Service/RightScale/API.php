<?php

namespace Buzz\Service\RightScale;

use Buzz\Service;

class API extends Service\AbstractService
{
  protected function configure()
  {
    $this->setName('rightscale');
  }
}
