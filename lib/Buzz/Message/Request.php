<?php

namespace Buzz\Message;

use Buzz\Util\Url;

class Request extends AbstractMessage implements RequestInterface
{
    private $method;
    private $resource;
    private $host;
    private $protocolVersion = 1.0;

    /**
     * Constructor.
     *
     * @param string $method
     * @param string $resource
     * @param string $host
     */
    public function __construct($method = self::METHOD_GET, $resource = '/', $host = null)
    {
        $this->method = strtoupper($method);
        $this->resource = $resource;
        $this->host = $host;
    }

    /**
     * {@inheritDoc}
     */
    public function setHeaders(array $headers)
    {
        parent::setHeaders(array());

        foreach ($this->flattenHeaders($headers) as $header) {
            $this->addHeader($header);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function addHeader($header)
    {
        if (0 === stripos(substr($header, -8), 'HTTP/1.') && 3 == count($parts = explode(' ', $header))) {
            list($method, $resource, $protocolVersion) = $parts;

            $this->setMethod($method);
            $this->setResource($resource);
            $this->setProtocolVersion((float) substr($protocolVersion, 5));
        } else {
            parent::addHeader($header);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritDoc}
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * {@inheritDoc}
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * {@inheritDoc}
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param float $protocolVersion
     */
    public function setProtocolVersion($protocolVersion)
    {
        $this->protocolVersion = $protocolVersion;
    }

    /**
     * {@inheritDoc}
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * A convenience method for getting the full URL of the current request.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getHost().$this->getResource();
    }

    /**
     * A convenience method for populating the current request from a URL.
     *
     * @param Url|string $url An URL
     */
    public function fromUrl($url)
    {
        if (!$url instanceof Url) {
            $url = new Url($url);
        }

        $url->applyToRequest($this);
    }

    /**
     * {@inheritDoc}
     */
    public function isSecure()
    {
        return 'https' == parse_url($this->getHost(), PHP_URL_SCHEME);
    }

    /**
     * Merges cookie headers on the way out.
     *
     * {@inheritDoc}
     */
    public function getHeaders()
    {
        return $this->mergeCookieHeaders(parent::getHeaders());
    }

    /**
     * Returns a string representation of the current request.
     *
     * {@inheritDoc}
     */
    public function __toString()
    {
        $string = sprintf("%s %s HTTP/%.1f\r\n", $this->getMethod(), $this->getResource(), $this->getProtocolVersion());

        if ($host = $this->getHost()) {
            $string .= 'Host: '.$host."\r\n";
        }

        if ($parent = trim(parent::__toString())) {
            $string .= $parent."\r\n";
        }

        return $string;
    }

    // private

    private function mergeCookieHeaders(array $headers)
    {
        $cookieHeader = null;
        $needle = 'Cookie:';

        foreach ($headers as $i => $header) {
            if (0 !== stripos($header, $needle)) {
                continue;
            }

            if (null === $cookieHeader) {
                $cookieHeader = $i;
            } else {
                $headers[$cookieHeader] .= '; '.trim(substr($header, strlen($needle)));
                unset($headers[$i]);
            }
        }

        return array_values($headers);
    }
}
