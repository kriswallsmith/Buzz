<?php

namespace Buzz\Client;

use Buzz\Message;

abstract class AbstractClient
{
    protected $ignoreErrors = true;
    protected $maxRedirects = 5;
    protected $timeout = 5;

    protected $auth = array(
        'user' => null,
        'pass' => null
    );

    public function setAuth($user, $pass = null)
    {
        $this->auth['user'] = $user;
        $this->auth['pass'] = $pass;
    }

    public function getAuth()
    {
        return null !== $this->auth['user'] ? $this->auth['user'].':'.$this->auth['pass'] : null;
    }

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
}
