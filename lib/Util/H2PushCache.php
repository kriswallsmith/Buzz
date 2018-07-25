<?php

declare(strict_types=1);

namespace Buzz\Util;

use Buzz\Message\ResponseBuilder;
use Nyholm\Psr7\Factory\MessageFactory;

class H2PushCache
{
    static private $cache = [];
    static private $pushHandles = [];
    static $transfers = 0;

    static function addPushHandle($headers, $handle)
    {
        foreach ($headers as $header) {
            if (strpos($header, ':path:') === 0) {
                $path = substr($header, 6);
                $url = curl_getinfo($handle)['url'];
                $url = str_replace(
                    parse_url($url, PHP_URL_PATH),
                    $path,
                    $url
                );
                static::$pushHandles[$url] = $handle;
            }
        }
    }

    static function add($handle)
    {
        $found = false;
        foreach (static::$pushHandles as $url => $h) {
            if ($handle == $h) {
                $found = $url;
            }
        }

        if (!$found) {
            $found = curl_getinfo($handle)['url'];
        }

        // TODO
        //$this->parseError($request, curl_errno($handle), $handle);

        $x = curl_multi_getcontent($handle);
        $headerSize = curl_getinfo($handle, CURLINFO_HEADER_SIZE);

        static::$cache[$found] = 'x';
    }

    static function exists($url)
    {
        return isset(static::$cache[$url]);
    }

    static function get($url)
    {
        return static::$cache[$url];
    }
}