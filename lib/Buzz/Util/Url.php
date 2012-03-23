<?php

namespace Buzz\Util;

class Url
{
    private $url;
    private $components;

    /**
     * Constructor.
     *
     * @param string $url The URL
     *
     * @throws InvalidArgumentException If the URL is invalid
     */
    public function __construct($url)
    {
        $components = parse_url($url);

        if (false === $components) {
            throw new \InvalidArgumentException(sprintf('The URL "%s" is invalid.', $url));
        }

        // support scheme-less URLs
        if (!isset($components['host']) && 0 !== strpos($components['path'], '/')) {
            list($host, $path) = explode('/', $components['path'], 2);
            $components['host'] = $host;
            $components['path'] = '/'.$path;
        }

        $this->url = $url;
        $this->components = $components;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getScheme()
    {
        return $this->parseUrl('scheme');
    }

    public function getHostname()
    {
        return $this->parseUrl('host');
    }

    public function getPort()
    {
        return $this->parseUrl('port');
    }

    public function getUser()
    {
        return $this->parseUrl('user');
    }

    public function getPassword()
    {
        return $this->parseUrl('pass');
    }

    public function getPath()
    {
        return $this->parseUrl('path');
    }

    public function getQueryString()
    {
        return $this->parseUrl('query');
    }

    public function getFragment()
    {
        return $this->parseUrl('fragment');
    }

    /**
     * Returns a host string that combines scheme, hostname and port.
     *
     * @return string A host value for an HTTP message
     */
    public function getHost()
    {
        if ($hostname = $this->parseUrl('host')) {
            $host  = $this->parseUrl('scheme', 'http');
            $host .= '://';
            $host .= $hostname;

            if ($port = $this->parseUrl('port')) {
                $host .= ':'.$port;
            }

            return $host;
        }
    }

    /**
     * Returns a resource string that combines path and query string.
     *
     * @return string A resource value for an HTTP message
     */
    public function getResource()
    {
        $resource = $this->parseUrl('path', '/');

        if ($query = $this->parseUrl('query')) {
            $resource .= '?'.$query;
        }

        return $resource;
    }

    private function parseUrl($component = null, $default = null)
    {
        if (null === $component) {
            return $this->components;
        } elseif (isset($this->components[$component])) {
            return $this->components[$component];
        } else {
            return $default;
        }
    }
}
