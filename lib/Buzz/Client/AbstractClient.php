<?php

namespace Buzz\Client;

use Buzz\Message;

abstract class AbstractClient
{
    protected $ignoreErrors = true;
    protected $maxRedirects = 5;
    protected $timeout = 5;

    protected $proxyIp;
    protected $proxyAuth = array(
        'user' => null,
        'pass' => null
    );

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

    public function setProxyIp($ip, $port = null)
    {
        $this->proxyIp = $ip.(null !== $port ? ':'.$port : '');
    }

    public function getProxyIp()
    {
        return $this->proxyIp;
    }

    public function setProxyAuth($user, $pass = null)
    {
        $this->proxyAuth['user'] = $user;
        $this->proxyAuth['pass'] = $pass;
    }

    public function getProxyAuth()
    {
        return null !== $this->proxyAuth['user'] ? $this->proxyAuth['user'].':'.$this->proxyAuth['pass'] : null;
    }
}
