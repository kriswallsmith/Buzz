<?php

namespace Buzz\Client;

use Buzz\Message;

class Curl extends AbstractClient implements ClientInterface
{
    protected $curl;

    static protected function createCurlHandle()
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);

        return $curl;
    }

    static protected function setCurlOptsFromRequest($curl, Message\Request $request)
    {
        $options = array(
            CURLOPT_CUSTOMREQUEST => $request->getMethod(),
            CURLOPT_URL           => $request->getHost().$request->getResource(),
            CURLOPT_HTTPHEADER    => $request->getHeaders(),
            CURLOPT_HTTPGET       => false,
            CURLOPT_NOBODY        => false,
            CURLOPT_POSTFIELDS    => null,
        );

        switch ($request->getMethod()) {
            case Message\Request::METHOD_HEAD:
                $options[CURLOPT_NOBODY] = true;
                break;

            case Message\Request::METHOD_GET:
                $options[CURLOPT_HTTPGET] = true;
                break;

            case Message\Request::METHOD_POST:
            case Message\Request::METHOD_PUT:
            case Message\Request::METHOD_DELETE:
            case Message\Request::METHOD_PATCH:
                $options[CURLOPT_POSTFIELDS] = $fields = self::getPostFields($request);

                // remove the content-type header
                if (is_array($fields)) {
                    $options[CURLOPT_HTTPHEADER] = array_filter($options[CURLOPT_HTTPHEADER], function($header)
                    {
                        return 0 !== stripos($header, 'Content-Type: ');
                    });
                }
                break;
        }

        curl_setopt_array($curl, $options);
    }

    static protected function getLastResponse($raw)
    {
        $parts = preg_split('/((?:\\r?\\n){2})/', $raw, -1, PREG_SPLIT_DELIM_CAPTURE);
        for ($i = count($parts) - 3; $i >= 0; $i -= 2) {
            if (0 === stripos($parts[$i], 'http')) {
                return implode('', array_slice($parts, $i));
            }
        }

        return $raw;
    }

    /**
     * Returns a value for the CURLOPT_POSTFIELDS option.
     *
     * @return string|array A post fields value
     */
    static private function getPostFields(Message\Request $request)
    {
        if (!$request instanceof Message\FormRequest) {
            return $request->getContent();
        }

        $fields = $request->getFields();
        $multipart = false;

        foreach ($fields as $name => $value) {
            if ($value instanceof Message\FormUpload) {
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

    public function __construct()
    {
        $this->curl = static::createCurlHandle();
    }

    public function getCurl()
    {
        return $this->curl;
    }

    public function send(Message\Request $request, Message\Response $response)
    {
        if (false === is_resource($this->curl)) {
            $this->curl = static::createCurlHandle();
        }

        $this->prepare($request, $response, $this->curl);

        $data = curl_exec($this->curl);
        if (false === $data) {
            $errorMsg = curl_error($this->curl);
            $errorNo  = curl_errno($this->curl);

            throw new \RuntimeException($errorMsg, $errorNo);
        }

        $response->fromString(static::getLastResponse($data));
    }

    protected function prepare(Message\Request $request, Message\Response $response, $curl)
    {
        static::setCurlOptsFromRequest($curl, $request);

        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0 < $this->maxRedirects);
        curl_setopt($curl, CURLOPT_MAXREDIRS, $this->maxRedirects);
        curl_setopt($curl, CURLOPT_FAILONERROR, !$this->ignoreErrors);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->verifyPeer);
    }

    public function __destruct()
    {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
    }
}
