<?php

namespace Buzz\Client;

use Buzz\Exception\RequestException;
use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;
use Buzz\Exception\LogicException;

class Curl extends AbstractCurl
{
    private $lastCurl;

    public function send(RequestInterface $request, MessageInterface $response, array $options = array())
    {
        if (is_resource($this->lastCurl)) {
            curl_close($this->lastCurl);
        }

        $this->lastCurl = static::createCurlHandle();
        $this->prepare($this->lastCurl, $request, $options);

        $data = curl_exec($this->lastCurl);

        if (false === $data) {
            $errorMsg = curl_error($this->lastCurl);
            $errorNo  = curl_errno($this->lastCurl);

            $e = new RequestException($errorMsg, $errorNo);
            $e->setRequest($request);

            throw $e;
        }

        static::populateResponse($this->lastCurl, $data, $response);
    }

    /**
     * Introspects the last cURL request.
     *
     * @param int $opt
     *
     * @return string|array
     * @throws LogicException
     *
     * @see curl_getinfo()
     *
     * @throws LogicException If there is no cURL resource
     */
    public function getInfo($opt = 0)
    {
        if (!is_resource($this->lastCurl)) {
            throw new LogicException('There is no cURL resource');
        }

        return 0 === $opt ? curl_getinfo($this->lastCurl) : curl_getinfo($this->lastCurl, $opt);
    }

    public function __destruct()
    {
        if (is_resource($this->lastCurl)) {
            curl_close($this->lastCurl);
        }
    }
}
