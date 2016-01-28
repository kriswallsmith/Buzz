<?php

namespace Buzz\Client;

use Buzz\Entity\Header;
use Buzz\Message\Form\FormRequestInterface;
use Buzz\Message\Form\FormUploadInterface;
use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;
use Buzz\Exception\ClientException;
use Buzz\Exception\InvalidArgumentException;

/**
 * Base client class with helpers for working with cURL.
 */
abstract class AbstractCurl extends AbstractClient
{
    protected $options = array();
    protected static $wipHeaders = array();

    public function __construct()
    {
        if (defined('CURLOPT_PROTOCOLS')) {
            $this->options = array(
                CURLOPT_PROTOCOLS       => CURLPROTO_HTTP | CURLPROTO_HTTPS,
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
        curl_setopt(
            $curl,
            CURLOPT_HEADERFUNCTION,
            function ($curl, $headerLine) {
                return static::addHeadersData($curl, $headerLine);
            }
        );

        return $curl;
    }

    /**
     * Populates a response object.
     *
     * @param resource         $curl     A cURL resource
     * @param string           $raw      The raw response string
     * @param MessageInterface $response The response object
     */
    protected static function populateResponse($curl, $raw, MessageInterface $response)
    {
        $curlResourceId = static::getResourceId($curl);
        $headers = self::$wipHeaders[$curlResourceId];
        $response->setHeaders($headers->getLastRedirectionDataList());
        $response->setContent(strlen($raw) > $headers->getSize() ? substr($raw, $headers->getSize()) : '');
    }

    /**
     * Sets options on a cURL resource based on a request.
     */
    private static function setOptionsFromRequest($curl, RequestInterface $request)
    {
        $options = array(
            CURLOPT_HTTP_VERSION  => $request->getProtocolVersion(
            ) == 1.0 ? CURL_HTTP_VERSION_1_0 : CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $request->getMethod(),
            CURLOPT_URL           => $request->getHost().$request->getResource(),
            CURLOPT_HTTPHEADER    => $request->getHeaders(),
        );

        switch ($request->getMethod()) {
            case RequestInterface::METHOD_HEAD:
                $options[CURLOPT_NOBODY] = true;
                break;

            case RequestInterface::METHOD_GET:
                $options[CURLOPT_HTTPGET] = true;
                break;

            case RequestInterface::METHOD_POST:
            case RequestInterface::METHOD_PUT:
            case RequestInterface::METHOD_DELETE:
            case RequestInterface::METHOD_PATCH:
            case RequestInterface::METHOD_OPTIONS:
                $options[CURLOPT_POSTFIELDS] = $fields = static::getPostFields($request);

                // remove the content-type header
                if (is_array($fields)) {
                    $options[CURLOPT_HTTPHEADER] = array_filter(
                        $options[CURLOPT_HTTPHEADER],
                        function ($header) {
                            return 0 !== stripos($header, 'Content-Type: ');
                        }
                    );
                }

                break;
        }

        curl_setopt_array($curl, $options);
    }

    /**
     * Returns a value for the CURLOPT_POSTFIELDS option.
     *
     * @return string|array A post fields value
     */
    private static function getPostFields(RequestInterface $request)
    {
        if (!$request instanceof FormRequestInterface) {
            return $request->getContent();
        }

        $fields = $request->getFields();
        $multipart = false;

        foreach ($fields as $name => $value) {
            if (!$value instanceof FormUploadInterface) {
                continue;
            }

            if (!$file = $value->getFile()) {
                return $request->getContent();
            }

            $multipart = true;

            if (version_compare(PHP_VERSION, '5.5', '>=')) {
                $curlFile = new \CURLFile($file);
                if ($contentType = $value->getContentType()) {
                    $curlFile->setMimeType($contentType);
                }

                if (basename($file) != $value->getFilename()) {
                    $curlFile->setPostFilename($value->getFilename());
                }

                $fields[$name] = $curlFile;
            } else {
                // replace value with upload string
                $fields[$name] = '@'.$file;

                if ($contentType = $value->getContentType()) {
                    $fields[$name] .= ';type='.$contentType;
                }
                if (basename($file) != $value->getFilename()) {
                    $fields[$name] .= ';filename='.$value->getFilename();
                }
            }
        }

        return $multipart ? $fields : http_build_query($fields, '', '&');
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
     */
    protected function prepare($curl, RequestInterface $request, array $options = array())
    {
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

    /**
     * Adds the provided head line length to the total header size of
     * the current request.
     *
     * @param resource $curl       A cURL resource. It is not used in the method, but is necessary to declare it
     *                             because this method is used as curl_setopt CURLOPT_HEADERFUNCTION callback
     * @param string   $headerLine Header row
     *
     * @return int String length of the provided header line
     */
    protected static function addHeadersData($curl, $headerLine)
    {
        $curlResourceId = static::getResourceId($curl);

        $headers = (!isset(self::$wipHeaders[$curlResourceId]))
            ? self::$wipHeaders[$curlResourceId] = new Header()
            : self::$wipHeaders[$curlResourceId];

        $headers->addDataList($headerLine);

        return strlen($headerLine);
    }

    protected static function getResourceId($resource)
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException('Provided param is not a curl resource object.');
        }

        $resourceToString = (string) $resource;
        $explodedString = explode('#', $resourceToString);
        return array_pop($explodedString);
    }

    protected static function unsetWipHeader($curl)
    {
        $curlResourceId = static::getResourceId($curl);
        if (isset(self::$wipHeaders[$curlResourceId])) {
            unset(self::$wipHeaders[$curlResourceId]);
        }
    }
}