<?php

declare(strict_types=1);

namespace Buzz\Client;

use Buzz\Configuration\ParameterBag;
use Buzz\Message\HeaderConverter;
use Buzz\Exception\ClientException;
use Buzz\Exception\NetworkException;
use Buzz\Exception\RequestException;
use Buzz\Message\ResponseBuilder;
use Nyholm\Psr7\Factory\MessageFactory;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Base client class with helpers for working with cURL.
 */
abstract class AbstractCurl extends AbstractClient
{
    private $handles = [];

    private $maxHandles = 5;

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('curl', []);
        $resolver->setAllowedTypes('curl', ['array']);
    }

    /**
     * Creates a new cURL resource.
     *
     * @return resource A new cURL resource
     *
     * @throws ClientException If unable to create a cURL resource
     */
    protected function createHandle()
    {
        $curl = $this->handles ? array_pop($this->handles) : curl_init();
        if (false === $curl) {
            throw new ClientException('Unable to create a new cURL handle');
        }

        return $curl;
    }

    /**
     * Release a cUrl resource. This function is from Guzzle.
     *
     * @param resource $curl
     */
    protected function releaseHandle($curl): void
    {
        if (count($this->handles) >= $this->maxHandles) {
            curl_close($curl);
        } else {
            // Remove all callback functions as they can hold onto references
            // and are not cleaned up by curl_reset. Using curl_setopt_array
            // does not work for some reason, so removing each one
            // individually.
            curl_setopt($curl, CURLOPT_HEADERFUNCTION, null);
            curl_setopt($curl, CURLOPT_READFUNCTION, null);
            curl_setopt($curl, CURLOPT_WRITEFUNCTION, null);
            curl_setopt($curl, CURLOPT_PROGRESSFUNCTION, null);
            curl_reset($curl);
            $this->handles[] = $curl;
        }
    }

    /**
     * Prepares a cURL resource to send a request.
     *
     * @param resource         $curl
     * @param RequestInterface $request
     * @param ParameterBag     $options
     *
     * @return ResponseBuilder
     */
    protected function prepare($curl, RequestInterface $request, ParameterBag $options): ResponseBuilder
    {
        if (defined('CURLOPT_PROTOCOLS')) {
            curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
            curl_setopt($curl, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        }

        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);

        $this->setOptionsFromParameterBag($curl, $options);
        $this->setOptionsFromRequest($curl, $request);

        $responseBuilder = new ResponseBuilder(new MessageFactory());
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, function ($ch, $data) use ($responseBuilder) {
            $str = trim($data);
            if ('' !== $str) {
                if (0 === strpos(strtolower($str), 'http/')) {
                    $responseBuilder->setStatus($str);
                } else {
                    $responseBuilder->addHeader($str);
                }
            }

            return strlen($data);
        });

        curl_setopt($curl, CURLOPT_WRITEFUNCTION, function ($ch, $data) use ($responseBuilder) {
            return $responseBuilder->writeBody($data);
        });

        // apply additional options
        curl_setopt_array($curl, $options->get('curl'));

        return $responseBuilder;
    }

    /**
     * Sets options on a cURL resource based on a request.
     *
     * @param resource         $curl    A cURL resource
     * @param RequestInterface $request A request object
     */
    private function setOptionsFromRequest($curl, RequestInterface $request): void
    {
        $options = [
            CURLOPT_CUSTOMREQUEST => $request->getMethod(),
            CURLOPT_URL => $request->getUri()->__toString(),
            CURLOPT_HTTPHEADER => HeaderConverter::toBuzzHeaders($request->getHeaders()),
        ];

        if (0 !== $version = $this->getProtocolVersion($request)) {
            $options[CURLOPT_HTTP_VERSION] = $version;
        }

        if ($request->getUri()->getUserInfo()) {
            $options[CURLOPT_USERPWD] = $request->getUri()->getUserInfo();
        }

        switch (strtoupper($request->getMethod())) {
            case 'HEAD':
                $options[CURLOPT_NOBODY] = true;

                break;

            case 'GET':
                $options[CURLOPT_HTTPGET] = true;

                break;

            case 'POST':
            case 'PUT':
            case 'DELETE':
            case 'PATCH':
            case 'OPTIONS':
                $body = $request->getBody();
                $bodySize = $body->getSize();
                if (0 !== $bodySize) {
                    if ($body->isSeekable()) {
                        $body->rewind();
                    }

                    // Message has non empty body.
                    if (null === $bodySize || $bodySize > 1024 * 1024) {
                        // Avoid full loading large or unknown size body into memory
                        $options[CURLOPT_UPLOAD] = true;
                        if (null !== $bodySize) {
                            $options[CURLOPT_INFILESIZE] = $bodySize;
                        }
                        $options[CURLOPT_READFUNCTION] = function ($ch, $fd, $length) use ($body) {
                            return $body->read($length);
                        };
                    } else {
                        // Small body can be loaded into memory
                        $options[CURLOPT_POSTFIELDS] = (string) $body;
                    }
                }
        }

        curl_setopt_array($curl, $options);
    }

    /**
     * @param resource     $curl
     * @param ParameterBag $options
     */
    private function setOptionsFromParameterBag($curl, ParameterBag $options): void
    {
        $timeout = $options->get('timeout');
        $proxy = $options->get('proxy');
        if ($proxy) {
            curl_setopt($curl, CURLOPT_PROXY, $proxy);
        }

        $canFollow = !ini_get('safe_mode') && !ini_get('open_basedir') && $options->get('allow_redirects');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $canFollow);
        curl_setopt($curl, CURLOPT_MAXREDIRS, $canFollow ? $options->get('max_redirects') : 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $options->get('verify') ? 1 : 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $options->get('verify') ? 2 : 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    }

    /**
     * @param RequestInterface $request
     * @param int              $errno
     * @param resource         $curl
     *
     * @throws NetworkException
     * @throws RequestException
     */
    protected function parseError(RequestInterface $request, int $errno, $curl): void
    {
        switch ($errno) {
            case CURLE_OK:
                // All OK, create a response object
                break;
            case CURLE_COULDNT_RESOLVE_PROXY:
            case CURLE_COULDNT_RESOLVE_HOST:
            case CURLE_COULDNT_CONNECT:
            case CURLE_OPERATION_TIMEOUTED:
            case CURLE_SSL_CONNECT_ERROR:
                throw new NetworkException($request, curl_error($curl), $errno);
            default:
                throw new RequestException($request, curl_error($curl), $errno);
        }
    }

    private function getProtocolVersion(RequestInterface $request): int
    {
        switch ($request->getProtocolVersion()) {
            case '1.0':
                return CURL_HTTP_VERSION_1_0;
            case '1.1':
                return CURL_HTTP_VERSION_1_1;
            case '2.0':
                if (defined('CURL_HTTP_VERSION_2_0')) {
                    return CURL_HTTP_VERSION_2_0;
                }

                throw new \UnexpectedValueException('libcurl 7.33 needed for HTTP 2.0 support');
            default:
                return 0;
        }
    }
}
