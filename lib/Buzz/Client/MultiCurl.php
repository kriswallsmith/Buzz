<?php

namespace Buzz\Client;

use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;
use Buzz\Exception\ClientException;

class MultiCurl extends AbstractCurl implements AsyncClientInterface
{
    protected $queue = array();

    /**
     * Populates the supplied response with the response for the supplied request.
     *
     * @param RequestInterface $request  A request object
     * @param MessageInterface $response A response object
     * @param Array $options Pass options 'callback' to handle responses as they complete and 'errback' to handle transport errors (timeout etc.)
     */
    public function send(RequestInterface $request, MessageInterface $response, array $options = array())
    {
        $this->queue[] = array($request, $response, $options);
    }

    public function isDone()
    {
        return empty($this->queue);
    }

    public function queueSize()
    {
        return count($this->queue);
    }

    public function proceed()
    {
        if ($this->isDone()) {
            return;
        }

        // Setup
        if (!isset($this->curlm)) {
            if (false === $this->curlm = curl_multi_init()) {
                throw new ClientException('Unable to create a new cURL multi handle');
            }
        }

        // prepare a cURL handle for each entry in the queue
        foreach (array_keys($this->queue) as $i) {
            if (!isset($this->queue[$i][3])) {
                list($request, $response, $options) = $this->queue[$i];
                $curl = static::createCurlHandle();
                $this->queue[$i][] = $curl;
                if (isset($options['callback'])) {
                    unset($options['callback']);
                }
                if (isset($options['errback'])) {
                    unset($options['errback']);
                }
                $this->prepare($curl, $request, $options);
                curl_multi_add_handle($this->curlm, $curl);
            }
        }

        // Process outstanding perform
        $active = null;
        do {
            $mrc = curl_multi_exec($this->curlm, $active);
        } while ($active && CURLM_CALL_MULTI_PERFORM == $mrc);

        // Handle any completed requests
        while ($done = curl_multi_info_read($this->curlm)) {
          foreach (array_keys($this->queue) as $i) {
                list($request, $response, $options, $curl) = $this->queue[$i];
                if ($curl === $done['handle']) {
                    if ($done['result'] === CURLE_OK) {
                      static::populateResponse($curl, curl_multi_getcontent($curl), $response);
                      curl_multi_remove_handle($this->curlm, $curl);
                      curl_close($curl);
                      unset($this->queue[$i]);
                      if (isset($options['callback'])) {
                          call_user_func($options['callback'], $request, $response, $options);
                      }
                    } else {
                        // Transport error
                        $error = self::$curl_error_codes[$done['result']];
                        curl_multi_remove_handle($this->curlm, $curl);
                        curl_close($curl);
                        unset($this->queue[$i]);
                        if (isset($options['errback'])) {
                            call_user_func($options['errback'], $request, $error, $done['result']);
                        }
                    }
                }
            }
        }

        // Cleanup
        if ($this->isDone()) {
            curl_multi_close($this->curlm);
            $this->curlm = null;
        }
    }

    public function flush()
    {
        while (!$this->isDone()) {
            $this->proceed();
        }
    }
}
