<?php

namespace Buzz\Client;

use Buzz\Message;

class Curl implements ClientInterface
{
    protected $curl;
    protected $maxRedirects = 5;
    protected $timeout = 5;

    static protected function createCurlHandle()
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);

        return $curl;
    }

    static protected function setCurlOptsFromRequest($curl, Message\Request $request)
    {
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $request->getMethod());
        curl_setopt($curl, CURLOPT_URL, $request->getUrl());
        curl_setopt($curl, CURLOPT_HTTPHEADER, $request->getHeaders());
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request->getContent());
    }

    public function __construct()
    {
        $this->curl = static::createCurlHandle();
    }

    public function getCurl()
    {
        return $this->curl;
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

    public function send(Message\Request $request, Message\Response $response)
    {
        $this->prepare($request, $response, $this->curl);
        $response->fromString($this->getLastResponse(curl_exec($this->curl)));
    }

    protected function prepare(Message\Request $request, Message\Response $response, $curl)
    {
        static::setCurlOptsFromRequest($curl, $request);

        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0 < $this->maxRedirects);
        curl_setopt($curl, CURLOPT_MAXREDIRS, $this->maxRedirects);
    }

    protected function getLastResponse($raw)
    {
        $parts = preg_split('/((?:\\r?\\n){2})/', $raw, -1, PREG_SPLIT_DELIM_CAPTURE);
        for ($i = count($parts) - 3; $i >= 0; $i -= 2) {
            if (0 === stripos($parts[$i], 'http')) {
                return implode('', array_slice($parts, $i));
            }
        }

        return $raw;
    }

    public function __destruct()
    {
        curl_close($this->curl);
    }
}
