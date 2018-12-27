<?php

declare(strict_types=1);

namespace Buzz\Client;

use Buzz\Configuration\ParameterBag;
use Buzz\Exception\ClientException;
use Buzz\Message\ResponseBuilder;
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
     * If a "callback" option is supplied, its value will be called when the
     * request completes. It is ONLY in the callback you will see the response
     * or an exception.
     *
     * This is a non-blocking function call.
     *
     * The callable should have the following signature:
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

        $this->addToQueue($request, $options);
    }

    /**
     * This is a blocking function call.
     */
    public function sendRequest(RequestInterface $request, array $options = []): ResponseInterface
    {
        $options = $this->validateOptions($options);
        $originalCallback = $options->get('callback');
        $responseToReturn = null;
        $options = $options->add(['callback' => function (RequestInterface $request, ResponseInterface $response = null, ClientException $e = null) use (&$responseToReturn, $originalCallback) {
            $responseToReturn = $response;
            $originalCallback($request, $response, $e);

            if (null !== $e) {
                throw $e;
            }
        }]);

        $this->addToQueue($request, $options);
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
        return \count($this->queue);
    }

    /**
     * Wait for all requests to finish.
     *
     * This is a blocking function call.
     *
     * This will not throw any exceptions. All exceptions are handled in the callback.
     */
    public function flush(): void
    {
        while (!empty($this->queue)) {
            $this->proceed();
        }
    }

    /**
     * See if any connection is ready to be processed.
     *
     * This is a non-blocking function call.
     *
     * @throws ClientException if we fail to initialized cUrl
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
        }

        foreach ($this->queue as $i => $queueItem) {
            if (2 !== \count($queueItem)) {
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

        $exception = null;
        do {
            // Start processing each handler in the stack
            $mrc = curl_multi_exec($this->curlm, $stillRunning);
        } while (CURLM_CALL_MULTI_PERFORM === $mrc);

        while ($info = curl_multi_info_read($this->curlm)) {
            // handle any completed requests
            if (CURLMSG_DONE !== $info['msg']) {
                continue;
            }

            $handled = false;
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
                    $handled = true;
                    $response = null;
                    $this->parseError($request, $info['result'], $curl);
                    $response = $responseBuilder->getResponse();
                } catch (\Throwable $e) {
                    if (null === $exception) {
                        $exception = $e;
                    }
                }

                // remove from queue
                curl_multi_remove_handle($this->curlm, $curl);
                $this->releaseHandle($curl);
                unset($this->queue[$i]);

                // callback
                \call_user_func($options->get('callback'), $request, $response, $exception);
                $exception = null;
            }

            if (!$handled) {
                // It must be a pushed response.
                $this->handlePushedResponse($info['handle']);
            }
        }

        // cleanup
        if (empty($this->queue)) {
            curl_multi_close($this->curlm);
            $this->curlm = null;
        }
    }

    /**
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return array
     */
    private function addToQueue(RequestInterface $request, ParameterBag $options): array
    {
        return $this->queue[] = [$request, $options];
    }
}
