<?php

namespace Buzz\Message;

class Request extends AbstractMessage
{
    const METHOD_GET    = 'GET';
    const METHOD_HEAD   = 'HEAD';
    const METHOD_POST   = 'POST';
    const METHOD_PUT    = 'PUT';
    const METHOD_DELETE = 'DELETE';

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

    public function setMethod($method)
    {
        $this->method = strtoupper($method);
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setHost($host)
    {
        $this->host = $host;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setProtocolVersion($protocolVersion)
    {
        $this->protocolVersion = $protocolVersion;
    }

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
     * @param string $url A URL
     *
     * @throws InvalidArgumentException If the URL is invalid
     */
    public function fromUrl($url)
    {
        $info = parse_url($url);

        if (false === $info) {
            throw new \InvalidArgumentException(sprintf('The URL "%s" is invalid.', $url));
        }

        // support scheme-less URLs
        if (!isset($info['host']) && 0 !== strpos($info['path'], '/')) {
            list($host, $path) = explode('/', $info['path'], 2);
            $info['host'] = $host;
            $info['path'] = '/'.$path;
        }

        $resource = isset($info['path']) ? $info['path'] : '/';
        if (isset($info['query'])) {
            $resource .= '?'.$info['query'];
        }
        $this->setResource($resource);

        if (isset($info['host'])) {
            $scheme = isset($info['scheme']) ? $info['scheme'] : 'http';
            $port = isset($info['port']) ? ':'.$info['port'] : '';
            $this->setHost($scheme.'://'.$info['host'].$port);
        }
    }

    /**
     * Returns true if the current request is secure.
     *
     * @return boolean
     */
    public function isSecure()
    {
        return 'https' == parse_url($this->getHost(), PHP_URL_SCHEME);
    }

    /**
     * Merges cookie headers on the way out.
     */
    public function getHeaders()
    {
        return $this->mergeCookieHeaders(parent::getHeaders());
    }

    /**
     * Returns a string representation of the current request.
     *
     * @return string
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
            if (0 !== strpos($header, $needle)) {
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
