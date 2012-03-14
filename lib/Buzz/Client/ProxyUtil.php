<?php

namespace Buzz\Client;

class ProxyUtil
{
    /**
     * Finds system proxy
     * 
     * If system environment is not passed $_SERVER will be used.
     * 
     * @param array|null $system
     */
    static public function getSystemProxy(array $system = null)
    {
        $system = $system ?: $_SERVER;
        if (isset($system['HTTP_PROXY']) || isset($system['http_proxy'])) {
            return isset($system['HTTP_PROXY']) ? $system['HTTP_PROXY'] : $system['http_proxy'];
        }

        return null;
    }

    /**
     * Parse proxy information from string
     * 
     * Response is an associative array with the following keys:
     * 
     *  - enabled:  whether or not proxy should be enabled
     *  - proxy:    actual proxy string (without authentication information)
     *  - username: username for connecting to proxy
     *  - password: password for connecting to proxy
     *  - raw:      raw proxy string
     * 
     * If $proxy is not passed, the system proxy will be used.
     * 
     * @param string|null $proxy
     */
    static public function parseProxy($proxy = null)
    {
        if (null === $proxy) {
            $proxy = static::getSystemProxy();
        }

        $response = array(
            'enabled' => false,
            'proxy' => null,
            'username' => null,
            'password' => null,
            'raw' => $proxy,
        );

        if ($proxy) {
            $response['enabled'] = true;
            if (preg_match("~^([\w]+://|)(?:([\w:]+)(?:@)|)(.+)$~", $proxy, $matches)) {
                $response['proxy'] = $matches[1].$matches[3];
                if ($matches[2]) {
                    if (false !== strpos($matches[2], ':')) {
                        $auth = explode(':', $matches[2]);
                        list ($response['username'], $response['password']) = explode(':', $matches[2]);
                    } else {
                        $response['username'] = $matches[2];
                    }
                }
            } else {
                $response['proxy'] = $proxy;
            }
        }

        return $response;
    }
}
