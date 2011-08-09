<?php

namespace Buzz\Client;

use Buzz\Message;

class Curl extends AbstractClient implements ClientInterface
{
    protected $curl;

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

    static protected function getLastResponse($raw)
    {
        $parts = preg_split('/((?:\\r?\\n){2})/', $raw, -1, PREG_SPLIT_DELIM_CAPTURE);
        for ($i = count($parts) - 3; $i >= 0; $i -= 2) {
            if (0 === stripos($parts[$i], 'http')) {
                return implode('', array_slice($parts, $i));
            }
        }

        return $raw;
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
        if (false === is_resource($this->curl)) {
            $this->curl = static::createCurlHandle();
        }

        $this->prepare($request, $response, $this->curl);

        $data = curl_exec($this->curl);
        if (false === $data) {
            $errorMsg = curl_error($this->curl);
            $errorNo  = curl_errno($this->curl);

            throw new \RuntimeException($errorMsg, $errorNo);
        }

        $response->fromString(static::getLastResponse($data));
    }

    protected function prepare(Message\Request $request, Message\Response $response, $curl)
    {
        static::setCurlOptsFromRequest($curl, $request);

        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0 < $this->maxRedirects);
        curl_setopt($curl, CURLOPT_MAXREDIRS, $this->maxRedirects);
        curl_setopt($curl, CURLOPT_FAILONERROR, !$this->ignoreErrors);
    }

    public function __destruct()
    {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
    }
}
