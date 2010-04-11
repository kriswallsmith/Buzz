<?php

namespace Buzz\Service\RightScale\Request;

class ServerRunScript extends AbstractServerRequest
{
  public function __construct($serverId, $rightScriptHref, $parameters = array(), $ignoreLock = false)
  {
    parent::__construct($serverId, 'run_script');

    $content = array(
      'server' => array(
        'right_script_href' => $rightScriptHref,
        'parameters'        => $parameters,
        'ignore_lock'       => $ignoreLock ? 'true' : 'false',
      ),
    );

    $this->setContent(http_build_query($content));

    $this->addHeader('Content-type: application/x-www-form-urlencoded');
    $this->addHeader('Content-length: '.strlen($this->getContent()));
  }
}
