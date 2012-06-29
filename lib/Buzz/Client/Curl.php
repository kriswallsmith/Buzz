<?php

namespace Buzz\Client;

use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;

class Curl extends AbstractCurl implements ClientInterface
{
    private $lastCurl;
    private $nbSends = 0;

    public function send(RequestInterface $request, MessageInterface $response, array $options = array())
    {
        if (is_resource($this->lastCurl)) {
            curl_close($this->lastCurl);
        }

        if ($this->nbSends++ > $this->getMaxRedirects()) {
            throw new \RunTimeException(sprintf('Exceeded maximum redirect attempts (%d)', $this->nbSends));
        }

        $this->lastCurl = static::createCurlHandle();
        $this->prepare($this->lastCurl, $request, $options);

        $data = $this->exec($this->lastCurl);

        if (false === $data) {
            $errorMsg = curl_error($this->lastCurl);
            $errorNo  = curl_errno($this->lastCurl);

            throw new \RuntimeException($errorMsg, $errorNo);
        }

        static::populateResponse($this->lastCurl, $data, $response);

        if ($response->isRedirection()) {
            $request->setResource($response->getHeader('Location'));
            $this->send($request, $response, $options);
        }
    }

    /**
     * Introspects the last cURL request.
     *
     * @see curl_getinfo()
     */
    public function getInfo($opt = 0)
    {
        if (!is_resource($this->lastCurl)) {
            throw new \LogicException('There is no cURL resource');
        }

        return curl_getinfo($this->lastCurl, $opt);
    }

    public function __destruct()
    {
        if (is_resource($this->lastCurl)) {
            curl_close($this->lastCurl);
        }
    }
}
