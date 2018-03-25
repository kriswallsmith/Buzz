<?php
declare(strict_types=1);

namespace Buzz\Client;

use Buzz\Configuration\ParameterBag;
use Buzz\Converter\HeaderConverter;
use Buzz\Exception\ClientException;
use Buzz\Exception\NetworkException;
use Buzz\Exception\RequestException;
use Nyholm\Psr7\Factory\MessageFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Base client class with helpers for working with cURL.
 */
abstract class AbstractCurl extends AbstractClient
{
    private $handles;

    private $maxHandles = 1;

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('curl', []);
        $resolver->setAllowedTypes('curl', ['array']);
    }

    /**
     * Creates a new cURL resource.
     *
     * @param RequestInterface $request
     * @param ParameterBag $options
     * @return resource A new cURL resource
     *
     * @throws ClientException If unable to create a cURL resource
     */
    protected function createHandle(RequestInterface $request, ParameterBag $options)
    {
        $curl = $this->handles ? array_pop($this->handles) : curl_init();
        if (false === $curl) {
            throw new ClientException('Unable to create a new cURL handle');
        }

        $this->prepare($curl, $request, $options);

        return $curl;
    }

    /**
     * Release a cUrl resource. This function is from Guzzle
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

    protected function createResponse(string $raw): ResponseInterface
    {
        $messageParts = preg_split("/\r?\n\r?\n/", $raw, 2);
        $filteredHeaders = $this->parseHeaders($messageParts[0]);
        $statusLine = array_shift($filteredHeaders);
        list($protocolVersion, $statusCode, $reasonPhrase) = $this->parseStatusLine($statusLine);
        $body = $messageParts[1];

        $response = (new MessageFactory())->createResponse(
            $statusCode,
            $reasonPhrase,
            HeaderConverter::toPsrHeaders($filteredHeaders),
            $body,
            $protocolVersion
        );
        $response->getBody()->rewind();

        return $response;
    }

    /**
     * A helper for getting the last set of headers.
     *
     * @param string $raw A string of many header chunks
     *
     * @return array An array of header lines
     */
    private function parseHeaders($raw)
    {
        $headers = array();
        foreach (preg_split('/(\\r?\\n)/', $raw) as $header) {
            if ($header) {
                $headers[] = $header;
            } else {
                $headers = array();
            }
        }

        return $headers;
    }

    /**
     * Prepares a cURL resource to send a request.
     *
     * @param resource $curl
     * @param RequestInterface $request
     * @param array $options
     */
    private function prepare($curl, RequestInterface $request, ParameterBag $options)
    {
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);

        $this->setOptionsFromRequest($curl, $request);

        $timeout = $options->get('timeout');
        $proxy = $options->get('proxy');
        if ($proxy) {
            curl_setopt($curl, CURLOPT_PROXY, $proxy);
        }

        $canFollow = !ini_get('safe_mode') && !ini_get('open_basedir') && $options->get('follow_redirects');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $canFollow);
        curl_setopt($curl, CURLOPT_MAXREDIRS, $canFollow ? $options->get('max_redirects') : 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $options->get('verify_peer') ? 1 : 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $options->get('verify_host') ? 2 : 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);

        if (defined('CURLOPT_PROTOCOLS')) {
            curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
            curl_setopt($curl, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        }

        // apply additional options
        curl_setopt_array($curl, $options->get('curl'));
    }

    /**
     * Sets options on a cURL resource based on a request.
     *
     * @param resource         $curl    A cURL resource
     * @param RequestInterface $request A request object
     */
    private function setOptionsFromRequest($curl, RequestInterface $request)
    {
        $options = array(
            CURLOPT_HTTP_VERSION  => $request->getProtocolVersion() === '1.0' ? CURL_HTTP_VERSION_1_0 : CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $request->getMethod(),
            CURLOPT_URL           => $request->getUri()->__toString(),
            CURLOPT_HTTPHEADER    => HeaderConverter::toBuzzHeaders($request->getHeaders()),
        );

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
                if ($bodySize !== 0) {
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
}
