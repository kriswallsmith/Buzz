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
     * Raw responses that the server has pushed to us.
     *
     * @var array
     */
    private $pushedResponses = [];

    /**
     * Curl handlers with unprocessed pushed responses.
     *
     * @var array
     */
    private $pushResponseHandles = [];

    /**
     * Callbacks that decides if a pushed request should be accepted or not.
     *
     * @var array
     */
    private $pushFunctions = [];

    /**
     * @var bool
     */
    private $serverPushSupported = true;

    /**
     * To work around bugs in PHP and GC.
     *
     * @var array
     */
    private $pushCb = [];

    /**
     * {@inheritdoc}
     */
    public function __construct($responseFactory, array $options = [])
    {
        parent::__construct($responseFactory, $options);

        if (
            \PHP_VERSION_ID < 70215 ||
            \PHP_VERSION_ID === 70300 ||
            \PHP_VERSION_ID === 70301 ||
            \PHP_VERSION_ID >= 80000 ||
            !(CURL_VERSION_HTTP2 & curl_version()['features'])
        ) {
            // Dont use HTTP/2 push when it's unsupported or buggy, see https://bugs.php.net/76675
            $this->serverPushSupported = false;
        }
    }

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

        $resolver->setDefault('push_function_callback', function ($parent, $pushed, $headers) {
            return CURL_PUSH_OK;
        });
        $resolver->setAllowedTypes('push_function_callback', ['callable', 'null']);

        $resolver->setDefault('use_pushed_response', true);
        $resolver->setAllowedTypes('use_pushed_response', 'boolean');
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
            $this->initMultiCurlHandle();
        }

        $this->initQueue();
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

            /** @var RequestInterface $request */
            /** @var ParameterBag $options */
            /** @var ResponseBuilder $responseBuilder */
            foreach ($this->queue as $i => list($request, $options, $curl, $responseBuilder)) {
                // Try to find the correct handle from the queue.
                if ($curl !== $info['handle']) {
                    continue;
                }

                $handled = true;
                $response = null;
                try {
                    $this->parseError($request, $info['result'], $curl);
                    $response = $responseBuilder->getResponse();
                    if ($options->get('expose_curl_info', false)) {
                        $response = $response->withHeader('__curl_info', (string) json_encode(curl_getinfo($curl)));
                    }
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

        $this->cleanup();
    }

    private function addPushHandle($headers, $handle)
    {
        foreach ($headers as $header) {
            if (0 === strpos($header, ':path:')) {
                $path = substr($header, 6);
                $url = (string) curl_getinfo($handle)['url'];
                $url = str_replace((string) parse_url($url, PHP_URL_PATH), $path, $url);
                $this->pushResponseHandles[$url] = $handle;
                break;
            }
        }
    }

    private function handlePushedResponse($handle)
    {
        $found = false;
        foreach ($this->pushResponseHandles as $url => $h) {
            // Weak comparison
            if ($handle == $h) {
                $found = $url;
            }
        }

        if (!$found) {
            $found = curl_getinfo($handle)['url'];
        }

        $content = curl_multi_getcontent($handle);
        // Check if we got some headers, if not, we do not bother to store it.
        if (0 !== $headerSize = curl_getinfo($handle, CURLINFO_HEADER_SIZE)) {
            $this->pushedResponses[$found] = ['content' => $content, 'headerSize' => $headerSize];
            unset($this->pushResponseHandles[$found]);
        }
    }

    private function hasPushResponse($url)
    {
        return isset($this->pushedResponses[$url]);
    }

    private function getPushedResponse($url)
    {
        $response = $this->pushedResponses[$url];
        unset($this->pushedResponses[$url]);

        return $response;
    }

    private function addToQueue(RequestInterface $request, ParameterBag $options): array
    {
        if (null !== $callback = $options->get('push_function_callback')) {
            $this->pushFunctions[] = $callback;
        }

        return $this->queue[] = [$request, $options];
    }

    /**
     * Create a multi curl handle and add some properties to it.
     */
    private function initMultiCurlHandle(): void
    {
        $this->curlm = curl_multi_init();
        if (false === $this->curlm) {
            throw new ClientException('Unable to create a new cURL multi handle');
        }

        if ($this->serverPushSupported) {
            $userCallbacks = $this->pushFunctions;

            curl_multi_setopt($this->curlm, CURLMOPT_PIPELINING, CURLPIPE_MULTIPLEX);
            // We need to use $this->pushCb[] because of a bug in PHP
            curl_multi_setopt(
                $this->curlm,
                CURLMOPT_PUSHFUNCTION,
                $this->pushCb[] = function ($parent, $pushed, $headers) use ($userCallbacks) {
                    // If any callback say no, then do not accept.
                    foreach ($userCallbacks as $callback) {
                        if (CURL_PUSH_DENY === $callback($parent, $pushed, $headers)) {
                            return CURL_PUSH_DENY;
                        }
                    }

                    curl_setopt($pushed, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($pushed, CURLOPT_HEADER, true);
                    $this->addPushHandle($headers, $pushed);

                    return CURL_PUSH_OK;
                }
            );
        }
    }

    /**
     * Loop over the queue and make sure every item (request) is initialized (ie, got a handle).
     */
    private function initQueue(): void
    {
        foreach ($this->queue as $i => $queueItem) {
            if (2 !== \count($queueItem)) {
                // We have already prepared this curl
                continue;
            }
            // prepare curl handle
            /** @var RequestInterface $request */
            /** @var ParameterBag $options */
            list($request, $options) = $queueItem;

            // Check if we have the response in cache already.
            if ($this->serverPushSupported
                && $options->get('use_pushed_response')
                && $this->hasPushResponse($request->getUri()->__toString())
            ) {
                $data = $this->getPushedResponse($request->getUri()->__toString());
                $response = (new ResponseBuilder($this->responseFactory))->getResponseFromRawInput(
                    $data['content'],
                    $data['headerSize']
                );
                \call_user_func($options->get('callback'), $request, $response, null);
                unset($this->queue[$i]);

                continue;
            }

            $curl = $this->createHandle();
            $responseBuilder = $this->prepare($curl, $request, $options);
            $this->queue[$i][] = $curl;
            $this->queue[$i][] = $responseBuilder;
            curl_multi_add_handle($this->curlm, $curl);
        }
    }

    /**
     * If we got no requests in the queue, do a clean up to save some memory.
     */
    private function cleanup(): void
    {
        if (empty($this->queue)) {
            curl_multi_close($this->curlm);
            $this->curlm = null;
            $this->pushFunctions = [];
            $this->pushCb = [];
        }
    }
}
