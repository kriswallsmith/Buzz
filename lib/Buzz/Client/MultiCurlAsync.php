<?php

namespace Buzz\Client;

use Buzz\Message;

class MultiCurlAsync extends MultiCurl implements AsyncBatchClientInterface
{
    protected $queue = array();
    protected $curlsBeforeSent = array();
    protected $curlsSent = array();

    protected $active = null;
    protected $mrc = null;
    protected $counter = 0;

    public function __construct()
    {
        $this->curl = curl_multi_init();
    }

    public function send(Message\Request $request, Message\Response $response, $curl = null, $callback = null, $callbackParameters = array())
    {
        $i = $this->counter++;
        $this->queue[$i] = array($request, $response, $curl, $callback, $callbackParameters);
        if (null === $curl) {
            $curl = $this->queue[$i][2] = static::createCurlHandle();
        }
        $this->curlsNotSent[$i] = $curl;

        $this->prepare($request, $response, $curl);
        curl_multi_add_handle($this->curl, $curl);

        $this->active = null;
        do {
            $this->mrc = curl_multi_exec($this->curl, $this->active);
            if (curl_getinfo($curl,CURLINFO_PRETRANSFER_TIME))  {
                //check if everyone already sent the request
                //FIXME PERF could maybe be faster
                foreach ($this->curlsNotSent as $j => $c) {
                    if ($c) {
                        if (!curl_getinfo($c,CURLINFO_PRETRANSFER_TIME)) {
                            $curl = $c;
                            continue 2;
                        } else {
                            //check if there's a callback
                            // if not, we don't need it in curlsSent
                            if ($this->queue[$i][3]) {
                                $this->curlsSent[$j] = $this->curlsNotSent[$j];
                            }
                            unset($this->curlsNotSent[$j]);
                        }
                    }
                }
                break;
            }
        } while ($this->active ||  CURLM_CALL_MULTI_PERFORM == $this->mrc);
    }

    
    public function flush()
    {
        $oldactive = null;
        while ($this->active && CURLM_OK == $this->mrc) {
            if (-1 != curl_multi_select($this->curl)) {
                do {
                    $this->mrc = curl_multi_exec($this->curl, $this->active);
                    if ($oldactive !== $this->active) {
                        foreach($this->curlsSent as $j => $c) {
                            // looks like Content-Length vs. downloaded size is the only reliable way to
                            // check if the download finished.
                            // FIXME: Check if there's another way 
                                    
                            // check if something is downloaded already
                            if ($downloaded = curl_getinfo($c,CURLINFO_SIZE_DOWNLOAD)) {
                                // if Content-Length is not set, we can't decide when the download is finished
                                // fire callback at the end.
                                $contentLength = curl_getinfo($c,CURLINFO_CONTENT_LENGTH_DOWNLOAD);
                                if ($contentLength < 0) {
                                    unset($this->curlsSent[$j]);
                                } else {
                                     // if we have downloaded enough bytes, fire callback and remove from queue
                                     if ($downloaded >= $contentLength) {
                                        list($request, $response, $curl, $cb, $cbparams) = $this->queue[$j];
                                        
                                        $response->fromString(static::getLastResponse(curl_multi_getcontent($curl)));
                                        if ($cb) {
                                            call_user_func_array($cb, array_merge(array($response), $cbparams));
                                        }

                                        // remove it from the queue
                                        curl_multi_remove_handle($this->curl, $curl);
                                        unset($this->queue[$j]);
                                        unset($this->curlsSent[$j]);
                                    }
                                }
                            }
                        }   
                        $oldactive = $this->active;
                    }
                } while (CURLM_CALL_MULTI_PERFORM == $this->mrc);
            }
        }

        // fire the rest which wasn't caught before;
        foreach ($this->queue as $queue) {
            list($request, $response, $curl,$cb, $cbparams) = $queue;
            $response->fromString(static::getLastResponse(curl_multi_getcontent($curl)));
            if ($cb) {
                call_user_func_array($cb, array_merge(array($response), $cbparams));
            }
            curl_multi_remove_handle($this->curl, $curl);
        }

        $this->queue = array();
    }
}
