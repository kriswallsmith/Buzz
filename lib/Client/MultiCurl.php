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
     * Raw responses that the server has pushed to us.
     * @var array
     */
    private $pushedResponses = [];

    /**
     * Curl handlers with unprocessed pushed responses
     * @var array
     */
    private $pushResponseHandles = [];

    /**
     * Callbacks that decides if a pushed request should be accepted or not.
     * @var array
     */
    private $pushFunctions = [];

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

        $this->addToQueue($request, $options);
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
        $resolver->setAllowedTypes('push_function_callback', 'callable');

        $resolver->setDefault('use_pushed_response', true);
        $resolver->setAllowedTypes('use_pushed_response', 'boolean');
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

            $userCallbacks = $this->pushFunctions;
            $cb = function ($parent, $pushed, $headers) use ($userCallbacks) {
                // If any callback say no, then do not accept.
                foreach ($userCallbacks as $callback) {
                    if (CURL_PUSH_DENY === $callback($parent, $pushed, $headers)) {
                        return CURL_PUSH_DENY;
                    }
                }

                curl_setopt($pushed, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($pushed, CURLOPT_HEADER, true);
                curl_setopt($pushed, CURLOPT_HEADERFUNCTION, null);
                curl_setopt($pushed, CURLOPT_WRITEFUNCTION, null);
                $this->addPushHandle($headers, $pushed);

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

            // Check if we have the response in cache already.
            if ($options->get('use_pushed_response') && $this->hasPushResponse($request->getUri()->__toString())) {
                $data = $this->getPushedResponse($request->getUri()->__toString());
                $response = (new ResponseBuilder($this->responseFactory))->getResponseFromRawInput($data['content'], $data['headerSize']);
                call_user_func($options->get('callback'), $request, $response, null);
                unset($this->queue[$i]);

                continue;
            }

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
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        $exception = null;

        // handle any completed requests
        while ($active && $mrc == CURLM_OK) {
            curl_multi_select($this->curlm);
            do {
                $mrc = curl_multi_exec($this->curlm, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);

            while ($info = curl_multi_info_read($this->curlm))
            {
                if ($info['msg'] == CURLMSG_DONE) {
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

                    if (!$handled) {
                        // It must be a pushed response.
                        $this->handlePushedResponse($info['handle']);
                    }
                }
            }
        }

        // cleanup
        if (empty($this->queue)) {
            curl_multi_close($this->curlm);
            $this->curlm = null;
            $this->pushFunctions = [];

            if (null !== $exception) {
                throw $exception;
            }
        }
    }


    private function addPushHandle($headers, $handle)
    {
        foreach ($headers as $header) {
            if (strpos($header, ':path:') === 0) {
                $path = substr($header, 6);
                $url = curl_getinfo($handle)['url'];
                $url = str_replace(
                    parse_url($url, PHP_URL_PATH),
                    $path,
                    $url
                );
                $this->pushResponseHandles[$url] = $handle;
            }
        }
    }

    private function handlePushedResponse($handle)
    {
        $found = false;
        foreach ($this->pushResponseHandles as $url => $h) {
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
        }
    }

    private function hasPushResponse($url)
    {
        return isset($this->pushedResponses[$url]);
    }

    private function getPushedResponse($url)
    {
        return $this->pushedResponses[$url];
    }

    /**
     * @param RequestInterface $request
     * @param array $options
     * @return array
     */
    private function addToQueue(RequestInterface $request, ParameterBag $options): array
    {
        $this->pushFunctions[] = $options->get('push_function_callback');

        return $this->queue[] = [$request, $options];
    }
}
