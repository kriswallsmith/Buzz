<?php

namespace Buzz\Client;

use Buzz\Message;

class MultiCurl extends Curl implements BatchClientInterface
{
    protected $queue = array();

    public function __construct()
    {
        $this->curl = curl_multi_init();
    }

    public function send(Message\Request $request, Message\Response $response, $curl = null)
    {
        $this->queue[] = array($request, $response, $curl);
    }

    public function flush()
    {
        foreach ($this->queue as $i => $queue) {
            list($request, $response, $curl) = $queue;

            if (null === $curl) {
                $curl = $this->queue[$i][2] = static::createCurlHandle();
            }

            $this->prepare($request, $response, $curl);
            curl_multi_add_handle($this->curl, $curl);
        }

        $active = null;
        do {
            $mrc = curl_multi_exec($this->curl, $active);
        } while (CURLM_CALL_MULTI_PERFORM == $mrc);

        while ($active && CURLM_OK == $mrc) {
            if (-1 != curl_multi_select($this->curl)) {
                do {
                    $mrc = curl_multi_exec($this->curl, $active);
                } while (CURLM_CALL_MULTI_PERFORM == $mrc);
            }
        }

        foreach ($this->queue as $queue) {
            list($request, $response, $curl) = $queue;
            $response->fromString(static::getLastResponse(curl_multi_getcontent($curl)));
            curl_multi_remove_handle($this->curl, $curl);
        }

        $this->queue = array();
    }

    public function getCurl($i = null)
    {
        if (null === $i) {
            return parent::getCurl();
        } elseif (isset($this->queue[$i])) {
            list($request, $response, $curl) = $this->queue[$i];
            return $curl;
        } else {
            throw new \InvalidArgumentException(sprintf('There is no cURL handler queued at position %s.', $i));
        }
    }

    public function __destruct()
    {
        curl_multi_close($this->curl);
    }
}
