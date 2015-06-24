<?php

namespace Buzz\Client;

use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;
use Buzz\Exception\ClientException;

class MultiCurl extends AbstractCurl implements BatchClientInterface
{
    private $queue = array();
    private $curlm;

    /**
     * Populates the supplied response with the response for the supplied request.
     *
     * The array of options will be passed to curl_setopt_array().
     *
     * If a "callback" option is supplied, its value will be called when the
     * request completes. The callable should have the following signature:
     *
     *     $callback = function($client, $request, $response, $options, $error) {
     *         if (!$error) {
     *             // success
     *         } else {
     *             // error ($error is one of the CURLE_* constants)
     *         }
     *     };
     *
     * @param RequestInterface $request  A request object
     * @param MessageInterface $response A response object
     * @param array            $options  An array of options
     */
    public function send(RequestInterface $request, MessageInterface $response, array $options = array())
    {
        $this->queue[] = array($request, $response, $options);
    }

    public function count()
    {
        return count($this->queue);
    }

    public function flush()
    {
        while ($this->queue) {
            $this->proceed();
        }
    }

    public function proceed()
    {
        if (!$this->queue) {
            return;
        }

        if (!$this->curlm && false === $this->curlm = curl_multi_init()) {
            throw new ClientException('Unable to create a new cURL multi handle');
        }

        foreach (array_keys($this->queue) as $i) {
            if (3 == count($this->queue[$i])) {
                // prepare curl handle
                list($request, , $options) = $this->queue[$i];
                $curl = static::createCurlHandle();

                // remove custom option
                unset($options['callback']);

                $this->prepare($curl, $request, $options);
                $this->queue[$i][] = $curl;
                curl_multi_add_handle($this->curlm, $curl);
            }
        }

        // process outstanding perform
        $active = null;
        do {
            $mrc = curl_multi_exec($this->curlm, $active);
        } while ($active && CURLM_CALL_MULTI_PERFORM == $mrc);

        // handle any completed requests
        while ($done = curl_multi_info_read($this->curlm)) {
            foreach (array_keys($this->queue) as $i) {
                list($request, $response, $options, $curl) = $this->queue[$i];

                if ($curl !== $done['handle']) {
                    continue;
                }

                // populate the response object
                if (CURLE_OK === $done['result']) {
                    static::populateResponse($curl, curl_multi_getcontent($curl), $response);
                }

                // remove from queue
                curl_multi_remove_handle($this->curlm, $curl);
                curl_close($curl);
                unset($this->queue[$i]);

                // callback
                if (isset($options['callback'])) {
                    call_user_func($options['callback'], $this, $request, $response, $options, $done['result']);
                }
            }
        }

        // cleanup
        if (!$this->queue) {
            curl_multi_close($this->curlm);
            $this->curlm = null;
        }
    }
}
