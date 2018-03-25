<?php
declare(strict_types=1);

namespace Buzz\Client;

use Buzz\Configuration\ParameterBag;
use Buzz\Converter\HeaderConverter;
use Buzz\Exception\ClientException;
use Nyholm\Psr7\Factory\MessageFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Base client class with helpers for working with cURL.
 */
abstract class AbstractCurl extends AbstractClient
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('curl', []);
        $resolver->setAllowedTypes('curl', ['array']);
    }

    /**
     * Creates a new cURL resource.
     *
     * @see curl_init()
     *
     * @return resource A new cURL resource
     *
     * @throws ClientException If unable to create a cURL resource
     */
    protected function createCurlHandle()
    {
        if (false === $curl = curl_init()) {
            throw new ClientException('Unable to create a new cURL handle');
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);

        return $curl;
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
     * Sets options on a cURL resource based on a request.
     *
     * @param resource         $curl    A cURL resource
     * @param RequestInterface $request A request object
     */
    private static function setOptionsFromRequest($curl, RequestInterface $request)
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
    protected function prepare($curl, RequestInterface $request, ParameterBag $options)
    {
        static::setOptionsFromRequest($curl, $request);
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
}
