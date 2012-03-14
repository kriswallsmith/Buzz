<?php

namespace Buzz\Client;

use Buzz\Message;

abstract class AbstractClient
{
    protected $ignoreErrors = true;
    protected $maxRedirects = 5;
    protected $timeout = 5;
    protected $verifyPeer = true;
    protected $proxyEnabled = false;
    protected $proxy;
    protected $proxyUsername;
    protected $proxyPassword;

    protected function getProxyAuth()
    {
        return null !== $this->getProxyUsername() ? $this->getProxyUsername().':'.$this->getProxyPassword() : null;
    }

    public function __construct()
    {
        $proxy = ProxyUtil::parseProxy();
        if ($proxy['enabled']) {
            $this->proxyEnabled = true;
            $this->proxy = $proxy['proxy'];
            $this->proxyUsername = $proxy['username'];
            $this->proxyPassword = $proxy['password'];
        }
    }

    /**
     * Enable or disable ignoring errors
     *
     * @param boolean $ignoreErrors
     * @return \Buzz\Client\AbstractClient
     */
    public function setIgnoreErrors($ignoreErrors)
    {
        $this->ignoreErrors = $ignoreErrors;

        return $this;
    }

    public function getIgnoreErrors()
    {
        return $this->ignoreErrors;
    }

    /**
     * Set maximum number of redirects
     *
     * @param number $maxRedirects
     * @return \Buzz\Client\AbstractClient
     */
    public function setMaxRedirects($maxRedirects)
    {
        $this->maxRedirects = $maxRedirects;

        return $this;
    }

    public function getMaxRedirects()
    {
        return $this->maxRedirects;

    }

    /**
     * Set request timeout in seconds
     * 
     * @param number $timeout
     * @return \Buzz\Client\AbstractClient
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Enable or disable validating certificates for HTTPS
     * 
     * @param boolean $verifyPeer
     * @return \Buzz\Client\AbstractClient
     */
    public function setVerifyPeer($verifyPeer)
    {
        $this->verifyPeer = $verifyPeer;

        return $this;
    }

    public function getVerifyPeer()
    {
        return $this->verifyPeer;
    }

    /**
     * Enable or disable proxy
     *
     * @param boolean $proxyEnabled
     * @return \Buzz\Client\AbstractClient
     */
    public function setProxyEnabled($proxyEnabled)
    {
        $this->proxyEnabled = $proxyEnabled;

        return $this;
    }

    public function getProxyEnabled()
    {
        return $this->proxyEnabled;
    }

    /**
     * Set proxy URL (should not include authentication information)
     * 
     * Examples:
     * 
     *  - http://proxy.example.com:3333
     *  - 10.4.4.5:2000
     * 
     * @param string $proxy
     * @return \Buzz\Client\AbstractClient
     */
    public function setProxy($proxy)
    {
        $this->proxy = $proxy;

        return $this;
    }

    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * Set username for proxy authentication
     * 
     * @param string $proxyUsername
     * @return \Buzz\Client\AbstractClient
     */
    public function setProxyUsername($proxyUsername)
    {
        $this->proxyUsername = $proxyUsername;

        return $this;
    }

    public function getProxyUsername()
    {
        return $this->proxyUsername;
    }

    /**
     * Set password for proxy authentication
     * 
     * @param string $proxyPassword
     * @return \Buzz\Client\AbstractClient
     */
    public function setProxyPassword($proxyPassword)
    {
        $this->proxyPassword = $proxyPassword;

        return $this;
    }

    public function getProxyPassword()
    {
        return $this->proxyPassword;
    }
}
