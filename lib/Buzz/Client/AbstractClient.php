<?php

namespace Buzz\Client;

abstract class AbstractClient implements ClientInterface
{
    protected $ignoreErrors = true;
    protected $maxRedirects = 5;
    protected $timeout = 5;
    protected $verifyPeer = true;
    protected $proxy;

    /**
     * @param boolean $ignoreErrors
     */
    public function setIgnoreErrors($ignoreErrors)
    {
        $this->ignoreErrors = $ignoreErrors;
    }

    /**
     * @return boolean
     */
    public function getIgnoreErrors()
    {
        return $this->ignoreErrors;
    }

    /**
     * @param int $maxRedirects
     */
    public function setMaxRedirects($maxRedirects)
    {
        $this->maxRedirects = $maxRedirects;
    }

    /**
     * @return int
     */
    public function getMaxRedirects()
    {
        return $this->maxRedirects;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param boolean $verifyPeer
     */
    public function setVerifyPeer($verifyPeer)
    {
        $this->verifyPeer = $verifyPeer;
    }

    /**
     * @return boolean
     */
    public function getVerifyPeer()
    {
        return $this->verifyPeer;
    }

    /**
     * @param string $proxy
     */
    public function setProxy($proxy)
    {
        $this->proxy = $proxy;
    }

    /**
     * @return string|null
     */
    public function getProxy()
    {
        return $this->proxy;
    }
}
