<?php

namespace Buzz\Client;

use Buzz\Message;

abstract class AbstractClient
{
    protected $ignoreErrors = true;
    protected $maxRedirects = 5;
    protected $timeout = 5;

    protected $authData;
    protected $authType;

    public function setIgnoreErrors($ignoreErrors)
    {
        $this->ignoreErrors = $ignoreErrors;
    }

    public function getIgnoreErrors()
    {
        return $this->ignoreErrors;
    }

    public function setMaxRedirects($maxRedirects)
    {
        $this->maxRedirects = $maxRedirects;
    }

    public function getMaxRedirects()
    {
        return $this->maxRedirects;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function setAuth($user, $password, $type)
    {
        $this->authData = $user . ':' . $password;
        $this->authType = $type;
    }
}