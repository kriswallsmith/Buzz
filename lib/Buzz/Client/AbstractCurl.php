<?php

namespace Buzz\Client;

use Buzz\Converter\HeaderConverter;
use Buzz\Converter\RequestConverter;
use Buzz\Converter\ResponseConverter;
use Buzz\Message\Form\FormRequestInterface;
use Buzz\Message\Form\FormUploadInterface;
use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface as BuzzRequestInterface;
use Buzz\Exception\ClientException;
use Buzz\Message\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Base client class with helpers for working with cURL.
 */
abstract class AbstractCurl extends AbstractClient
{
    protected $options = array();

    public function __construct()
    {
        if (defined('CURLOPT_PROTOCOLS')) {
            $this->options = array(
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            );
        }
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
    protected static function createCurlHandle()
    {
        if (false === $curl = curl_init()) {
            throw new ClientException('Unable to create a new cURL handle');
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);

        return $curl;
    }

    /**
     * Populates a response object.
     *
     * @param resource         $curl     A cURL resource
     * @param string           $raw      The raw response string
     * @param MessageInterface $response The response object
     *
     * @deprecated Will be removed in 1.0. Use createResponse instead.
     */
    protected static function populateResponse($curl, $raw, MessageInterface $response)
    {
        @trigger_error('AbstractCurl::populateResponse() is deprecated. Use AbstractCurl::createResponse instead.', E_USER_DEPRECATED);

        // fixes bug https://sourceforge.net/p/curl/bugs/1204/
        $version = curl_version();
        if (version_compare($version['version'], '7.30.0', '<')) {
            $pos = strlen($raw) - curl_getinfo($curl, CURLINFO_SIZE_DOWNLOAD);
        } else {
            $pos = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        }

        $response->setHeaders(static::getLastHeaders(rtrim(substr($raw, 0, $pos))));
        $response->setContent(strlen($raw) > $pos ? substr($raw, $pos) : '');
    }

    /**
     * @param $curl
     * @param $raw
     * @return ResponseInterface
     */
    protected function createResponse($curl, $raw)
    {
        // fixes bug https://sourceforge.net/p/curl/bugs/1204/
        $version = curl_version();
        if (version_compare($version['version'], '7.30.0', '<')) {
            $pos = strlen($raw) - curl_getinfo($curl, CURLINFO_SIZE_DOWNLOAD);
        } else {
            $pos = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        }

        // TODO rewrite me to avoid using BuzzResponse
        $response = new Response();
        $response->setHeaders(static::getLastHeaders(rtrim(substr($raw, 0, $pos))));
        $response->setContent(strlen($raw) > $pos ? substr($raw, $pos) : '');

        $response = ResponseConverter::psr7($response);

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
            CURLOPT_HTTP_VERSION  => $request->getProtocolVersion() == 1.0 ? CURL_HTTP_VERSION_1_0 : CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $request->getMethod(),
            CURLOPT_URL           => $request->getUri()->__toString(),
            CURLOPT_HTTPHEADER    => HeaderConverter::toBuzzHeaders($request->getHeaders()),
        );

        switch ($request->getMethod()) {
            case BuzzRequestInterface::METHOD_HEAD:
                $options[CURLOPT_NOBODY] = true;
                break;

            case BuzzRequestInterface::METHOD_GET:
                $options[CURLOPT_HTTPGET] = true;
                break;

            case BuzzRequestInterface::METHOD_POST:
            case BuzzRequestInterface::METHOD_PUT:
            case BuzzRequestInterface::METHOD_DELETE:
            case BuzzRequestInterface::METHOD_PATCH:
            case BuzzRequestInterface::METHOD_OPTIONS:
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
    private static function getLastHeaders($raw)
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
     * Stashes a cURL option to be set on send, when the resource is created.
     *
     * If the supplied value it set to null the option will be removed.
     *
     * @param integer $option The option
     * @param mixed   $value  The value
     *
     * @see curl_setopt()
     */
    public function setOption($option, $value)
    {
        if (null === $value) {
            unset($this->options[$option]);
        } else {
            $this->options[$option] = $value;
        }
    }

    /**
     * Prepares a cURL resource to send a request.
     *
     * @param $curl
     * @param RequestInterface $request
     * @param array $options
     */
    protected function prepare($curl, $request, array $options = array())
    {
        $request = RequestConverter::psr7($request);
        static::setOptionsFromRequest($curl, $request);

        // apply settings from client
        if ($this->getTimeout() < 1) {
            curl_setopt($curl, CURLOPT_TIMEOUT_MS, $this->getTimeout() * 1000);
        } else {
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->getTimeout());
        }

        if ($this->proxy) {
            curl_setopt($curl, CURLOPT_PROXY, $this->proxy);
        }

        $canFollow = !ini_get('safe_mode') && !ini_get('open_basedir');

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $canFollow && $this->getMaxRedirects() > 0);
        curl_setopt($curl, CURLOPT_MAXREDIRS, $canFollow ? $this->getMaxRedirects() : 0);
        curl_setopt($curl, CURLOPT_FAILONERROR, !$this->getIgnoreErrors());
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->getVerifyPeer());
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $this->getVerifyHost());

        // apply additional options
        curl_setopt_array($curl, $options + $this->options);
    }
}
