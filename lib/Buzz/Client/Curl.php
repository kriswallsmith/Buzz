<?php

namespace Buzz\Client;

use Buzz\Message;

class Curl extends AbstractClient implements ClientInterface
{
    protected $options = array();

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
                    $options[CURLOPT_HTTPHEADER] = array_filter($options[CURLOPT_HTTPHEADER], function($header) {
                        return 0 !== stripos($header, 'Content-Type: ');
                    });
                }

                break;
        }

        curl_setopt_array($curl, $options);
    }

    static protected function getLastHeaders($raw)
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

    /**
     * Stashes a cURL option to be set on send, when the resource is created.
     *
     * If the supplied value it set to null the option will be removed.
     *
     * @param integer $option The option
     * @param mixed   $value  The value
     */
    public function setOption($option, $value)
    {
        if (null === $value) {
            unset($this->options[$option]);
        } else {
            $this->options[$option] = $value;
        }
    }

    public function send(Message\Request $request, Message\Response $response)
    {
        $curl = static::createCurlHandle();

        $this->prepare($request, $response, $curl);

        $data = curl_exec($curl);

        if (false === $data) {
            $errorMsg = curl_error($curl);
            $errorNo  = curl_errno($curl);

            throw new \RuntimeException($errorMsg, $errorNo);
        }

        $pos = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $response->setHeaders(self::getLastHeaders(rtrim(substr($data, 0, $pos))));
        $response->setContent(substr($data, $pos));

        curl_close($curl);
    }

    protected function prepare(Message\Request $request, Message\Response $response, $curl)
    {
        static::setCurlOptsFromRequest($curl, $request);

        curl_setopt($curl, CURLOPT_TIMEOUT, $this->getTimeout());
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0 < $this->getMaxRedirects());
        curl_setopt($curl, CURLOPT_MAXREDIRS, $this->getMaxRedirects());
        curl_setopt($curl, CURLOPT_FAILONERROR, !$this->getIgnoreErrors());
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->getVerifyPeer());

        // finally apply the manually set options
        curl_setopt_array($curl, $this->options);
    }
}
