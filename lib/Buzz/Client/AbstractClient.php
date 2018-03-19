<?php
declare(strict_types=1);

namespace Buzz\Client;

use Psr\Http\Client\ClientInterface;

abstract class AbstractClient
{
    protected $ignoreErrors = true;
    protected $maxRedirects = 5;
    protected $timeout = 5;
    protected $verifyPeer = true;
    protected $verifyHost = 2;
    protected $proxy;

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

    public function setVerifyPeer($verifyPeer)
    {
        $this->verifyPeer = $verifyPeer;
    }

    public function getVerifyPeer()
    {
        return $this->verifyPeer;
    }

    public function getVerifyHost()
    {
        return $this->verifyHost;
    }

    public function setVerifyHost($verifyHost)
    {
        $this->verifyHost = $verifyHost;
    }

    public function setProxy($proxy)
    {
        $this->proxy = $proxy;
    }

    public function getProxy()
    {
        return $this->proxy;
    }

    protected function parseStatusLine(string $statusLine): array
    {
        $protocolVersion = null;
        $statusCode = 0;
        $reasonPhrase = null;

        if (2 <= count($parts = explode(' ', $statusLine, 3))) {
            $protocolVersion = (string) substr($parts[0], 5);
            $statusCode = (integer) $parts[1];
            $reasonPhrase = isset($parts[2]) ? $parts[2] : '';
        }

        return [$protocolVersion, $statusCode, $reasonPhrase];
    }
}
