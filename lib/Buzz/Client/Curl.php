<?php
declare(strict_types=1);

namespace Buzz\Client;


use Buzz\Exception\NetworkException;
use Buzz\Exception\RequestException;

use Buzz\Exception\LogicException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Curl extends AbstractCurl implements BuzzClientInterface
{
    private $lastCurl;

    public function sendRequest(RequestInterface $request, array $options = []): ResponseInterface
    {
        $options = $this->validateOptions($options);
        if (is_resource($this->lastCurl)) {
            curl_close($this->lastCurl);
        }

        $this->lastCurl = $this->createCurlHandle();
        $this->prepare($this->lastCurl, $request, $options);
        $data = curl_exec($this->lastCurl);
        $this->parseError($request, $this->lastCurl);

        return $this->createResponse($data);
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
