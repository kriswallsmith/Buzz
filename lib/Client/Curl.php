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
    public function sendRequest(RequestInterface $request, array $options = []): ResponseInterface
    {
        $options = $this->validateOptions($options);
        $curl = $this->createHandle($request, $options);
        try {
            $data = curl_exec($curl);
            $this->parseError($request, curl_errno($curl), $curl);
        } finally {
            $this->releaseHandle($curl);
        }

        return $this->createResponse($data);
    }
}
