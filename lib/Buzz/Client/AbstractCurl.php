<?php

namespace Buzz\Client;

use Buzz\Message\Form\FormRequestInterface;
use Buzz\Message\Form\FormUploadInterface;
use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;

/**
 * Base client class with helpers for working with cURL.
 */
abstract class AbstractCurl extends AbstractClient
{
    protected $options = array();

    /**
     * Creates a new cURL resource.
     *
     * @see curl_init()
     *
     * @return resource A new cURL resource
     */
    static protected function createCurlHandle()
    {
        if (false === $curl = curl_init()) {
            throw new \RuntimeException('Unable to create a new cURL handle');
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
     */
    static protected function populateResponse($curl, $raw, MessageInterface $response)
    {
        $pos = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

        $response->setHeaders(static::getLastHeaders(rtrim(substr($raw, 0, $pos))));
        $response->setContent(substr($raw, $pos));
    }

    /**
     * Sets options on a cURL resource based on a request.
     */
    static private function setOptionsFromRequest($curl, RequestInterface $request)
    {
        $options = array(
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
                $options[CURLOPT_POSTFIELDS] = $fields = static::getPostFields($request);

                // remove the content-type header
                if (is_array($fields)) {
                    $options[CURLOPT_HTTPHEADER] = array_filter($options[CURLOPT_HTTPHEADER], function($header) {
                        return 0 !== stripos($header, 'Content-Type: ');
                    });
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
    static private function getPostFields(RequestInterface $request)
    {
        if (!$request instanceof FormRequestInterface) {
            return $request->getContent();
        }

        $fields = $request->getFields();
        $multipart = false;

        foreach ($fields as $name => $value) {
            if ($value instanceof FormUploadInterface) {
                $multipart = true;

                if ($file = $value->getFile()) {
                    // replace value with upload string
                    $fields[$name] = '@'.$file;

                    if ($contentType = $value->getContentType()) {
                        $fields[$name] .= ';type='.$contentType;
                    }
                } else {
                    return $request->getContent();
                }
            }
        }

        return $multipart ? $fields : http_build_query($fields);
    }

    /**
     * A helper for getting the last set of headers.
     *
     * @param string $raw A string of many header chunks
     *
     * @return array An array of header lines
     */
    static private function getLastHeaders($raw)
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
     */
    protected function prepare($curl, RequestInterface $request, array $options = array())
    {
        static::setOptionsFromRequest($curl, $request);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0 < $this->getMaxRedirects());
        }

        // apply settings from client
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->getTimeout());
        curl_setopt($curl, CURLOPT_MAXREDIRS, $this->getMaxRedirects());
        curl_setopt($curl, CURLOPT_FAILONERROR, !$this->getIgnoreErrors());
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->getVerifyPeer());

        // apply additional options
        curl_setopt_array($curl, $options + $this->options);
    }

    /**
     * Execute a cURL resource.
     */
    protected function exec($ch)
    {
        return curl_exec($ch);
    }
}
