<?php

declare(strict_types=1);

namespace Buzz\Client;

use Buzz\Configuration\ParameterBag;
use Buzz\Exception\ExceptionInterface;
use Buzz\Exception\ClientException;
use Buzz\Message\ResponseBuilder;
use Buzz\Util\H2PushCache;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MultiCurl extends AbstractCurl implements BatchClientInterface, BuzzClientInterface
{
    private $queue = [];

    private $curlm;

    /**
     * Populates the supplied response with the response for the supplied request.
     *
     * The array of options will be passed to curl_setopt_array().
     *
     * If a "callback" option is supplied, its value will be called when the
     * request completes. The callable should have the following signature:
     *
     *     $callback = function($request, $response, $exception) {
     *         if (!$exception) {
     *             // success
     *         } else {
     *             // error ($error is one of the CURLE_* constants)
     *         }
     *     };
     */
    public function sendAsyncRequest(RequestInterface $request, array $options = []): void
    {
        $options = $this->validateOptions($options);

        $this->queue[] = [$request, $options];
    }

    public function sendRequest(RequestInterface $request, array $options = []): ResponseInterface
    {
        $options = $this->validateOptions($options);
        $originalCallback = $options->get('callback');
        $responseToReturn = null;
        $options = $options->add(['callback' => function (RequestInterface $request, ResponseInterface $response = null, ClientException $e = null) use (&$responseToReturn, $originalCallback) {
            $responseToReturn = $response;
            $originalCallback($request, $response, $e);
        }]);

        $this->queue[] = [$request, $options];
        $this->flush();

        return $responseToReturn;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('callback', function (RequestInterface $request, ResponseInterface $response = null, ClientException $e = null) {
        });
        $resolver->setAllowedTypes('callback', 'callable');
    }

    public function count(): int
    {
        return count($this->queue);
    }

    /**
     * @throws ClientException
     */
    public function flush(): void
    {
        while (!empty($this->queue)) {
            $this->proceed();
        }
    }

    /**
     * @throws ClientException
     */
    public function proceed(): void
    {
        if (empty($this->queue)) {
            return;
        }

        if (!$this->curlm) {
            if (false === $this->curlm = curl_multi_init()) {
                throw new ClientException('Unable to create a new cURL multi handle');
            }

            $cb = function ($parent, $pushed, $headers) {

                H2PushCache::addPushHandle($headers, $pushed);
                //$this->queue[] = ['request', ['options'], $pushed];


                return CURL_PUSH_OK;
            };

            curl_multi_setopt($this->curlm , CURLMOPT_PIPELINING, CURLPIPE_MULTIPLEX);
            curl_multi_setopt($this->curlm , CURLMOPT_PUSHFUNCTION, $cb);
        }

        foreach ($this->queue as $i => $queueItem) {
            if (2 !== count($queueItem)) {
                // We have already prepared this curl
                continue;
            }
            // prepare curl handle
            /** @var $request RequestInterface */
            /** @var $options ParameterBag */
            list($request, $options) = $queueItem;
            $curl = $this->createHandle();
            $responseBuilder = $this->prepare($curl, $request, $options);
            $this->queue[$i][] = $curl;
            $this->queue[$i][] = $responseBuilder;
            curl_multi_add_handle($this->curlm, $curl);
        }

        // process outstanding perform
        $active = null;
        do {
            $mrc = curl_multi_exec($this->curlm, $active);
            sleep(1);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        $exception = null;
        $responseBuilder = new ResponseBuilder($this->responseFactory);

        // handle any completed requests
        while ($active && $mrc == CURLM_OK) {
            curl_multi_select($this->curlm);
            do {
                $mrc = curl_multi_exec($this->curlm, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);

            while ($info = curl_multi_info_read($this->curlm))
            {
                if ($info['msg'] == CURLMSG_DONE) {
                    H2PushCache::add($info['handle']);
                    foreach (array_keys($this->queue) as $i) {
                        /** @var $request RequestInterface */
                        /** @var $options ParameterBag */
                        /** @var $responseBuilder ResponseBuilder */
                        list($request, $options, $curl, $responseBuilder) = $this->queue[$i];

                        // Try to find the correct handle from the queue.
                        if ($curl !== $info['handle']) {
                            continue;
                        }

                        try {
                            $response = null;
                            //$this->parseError($request, $info['result'], $curl);
                            //H2PushCache::add($curl);

                            // populate the response object
                            $raw = curl_multi_getcontent($curl);
                            //$response = $responseBuilder->getResponseFromRawInput($raw, curl_getinfo($curl, CURLINFO_HEADER_SIZE));
                            $response = $responseBuilder->getResponse();
                        } catch (ExceptionInterface $e) {
                            if (null === $exception) {
                                $exception = $e;
                            }
                        }

                        // remove from queue
                        curl_multi_remove_handle($this->curlm, $curl);
                        $this->releaseHandle($curl);
                        unset($this->queue[$i]);

                        // callback
                        call_user_func($options->get('callback'), $request, $response, $exception);
                    }
                }
            }
        }

        // cleanup
        if (empty($this->queue)) {
            curl_multi_close($this->curlm);
            $this->curlm = null;

            if (null !== $exception) {
                throw $exception;
            }
        }
    }
}
