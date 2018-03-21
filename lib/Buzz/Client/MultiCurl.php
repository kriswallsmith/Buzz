<?php
declare(strict_types=1);

namespace Buzz\Client;

use Buzz\Configuration\ParameterBag;
use Buzz\Exception\RequestException;
use Buzz\Exception\ClientException;
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
     *
     */
    public function sendAsyncRequest(RequestInterface $request, array $options = []): void
    {
        $options = $this->validateOptions($options);

        $this->queue[] = array($request, $options);
    }

    public function sendRequest(RequestInterface $request, array $options = []): ResponseInterface
    {
        $options = $this->validateOptions($options);
        $originalCallback = $options->get('callback');
        $responseToReturn = null;
        $options = $options->add(['callback' => function(RequestInterface $request, ResponseInterface $response = null, ClientException $e = null) use (&$responseToReturn, $originalCallback) {
            $responseToReturn = $response;
            $originalCallback($request, $response, $e);
        }]);

        $this->queue[] = array($request, $options);
        $this->flush();

        return $responseToReturn;
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('callback', function(RequestInterface $request, ResponseInterface $response = null, ClientException $e = null) {});
        $resolver->setAllowedTypes('callback', 'callable');
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
            // prepare curl handle
            /** @var $request RequestInterface */
            /** @var $options ParameterBag */
            list($request, $options) = $this->queue[$i];
            $curl = $this->createCurlHandle();

            $this->prepare($curl, $request, $options);
            $this->queue[$i][] = $curl;
            curl_multi_add_handle($this->curlm, $curl);
        }

        // process outstanding perform
        $active = null;
        do {
            $mrc = curl_multi_exec($this->curlm, $active);
        } while ($active && CURLM_CALL_MULTI_PERFORM == $mrc);

        $exception = null;
        // handle any completed requests
        while ($done = curl_multi_info_read($this->curlm)) {
            foreach (array_keys($this->queue) as $i) {
                /** @var $request RequestInterface */
                /** @var $options ParameterBag */
                list($request, $options, $curl) = $this->queue[$i];

                if ($curl !== $done['handle']) {
                    continue;
                }

                $response = null;
                // populate the response object
                if (CURLE_OK === $done['result']) {
                    $response = $this->createResponse($curl, curl_multi_getcontent($curl));
                } elseif (null === $exception) {
                    $errorMsg = curl_error($curl);
                    $errorNo  = curl_errno($curl);

                    $exception = new RequestException($request, $errorMsg, $errorNo);
                }

                // remove from queue
                curl_multi_remove_handle($this->curlm, $curl);
                curl_close($curl);
                unset($this->queue[$i]);

                // callback
                call_user_func($options->get('callback'), $request, $response, $exception);
            }
        }

        // cleanup
        if (!$this->queue) {
            curl_multi_close($this->curlm);
            $this->curlm = null;
        }

        if (null !== $exception) {
            throw $exception;
        }
    }
}
