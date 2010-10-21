<?php

namespace Buzz\Client;

use Buzz\Message;

class Curl implements ClientInterface
{
    protected $curl;

    static protected function createCurlHandle()
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

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

    public function send(Message\Request $request, Message\Response $response)
    {
        static::setCurlOptsFromRequest($this->curl, $request);
        $response->fromString(curl_exec($this->curl));
    }

    public function __destruct()
    {
        curl_close($this->curl);
    }
}
