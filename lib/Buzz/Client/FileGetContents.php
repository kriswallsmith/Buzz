<?php

namespace Buzz\Client;

use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;
use Buzz\Message\Response;
use Buzz\Message\Request;
use Buzz\Util\CookieJar;
use Buzz\Exception\ClientException;

class FileGetContents extends AbstractStream
{
    /**
     * @var CookieJar
     */
    protected $cookieJar;

    /**
     * @param CookieJar|null $cookieJar
     */
    public function __construct(CookieJar $cookieJar = null)
    {
        if ($cookieJar) {
            $this->setCookieJar($cookieJar);
        }
    }

    /**
     * @param CookieJar $cookieJar
     */
    public function setCookieJar(CookieJar $cookieJar)
    {
        $this->cookieJar = $cookieJar;
    }

    /**
     * @return CookieJar
     */
    public function getCookieJar()
    {
        return $this->cookieJar;
    }

    /**
     * @see ClientInterface
     *
     * @throws ClientException If file_get_contents() fires an error
     */
    public function send(RequestInterface $request, MessageInterface $response)
    {
        if ($cookieJar = $this->getCookieJar()) {
            $cookieJar->clearExpiredCookies();
            $cookieJar->addCookieHeaders($request);
        }

        $url = $request->getHost().$request->getResource();
        $currentRequest = $request;
        $allowedRedirects = $this->getMaxRedirects();
        while (true) {
            if ($allowedRedirects == 0) {
                throw new ClientException("Max number of redirects exceeded");
            }
            $context = stream_context_create($this->getStreamContextArray($currentRequest, 1));
            $level = error_reporting(0);
            $content = file_get_contents($url, 0, $context);
            error_reporting($level);
            if (false === $content) {
                $error = error_get_last();
                throw new ClientException($error['message']);
            }
            $tmp = new Response();
            $responseHeaders = $this->filterHeaders((array) $http_response_header);
            $tmp->setHeaders($responseHeaders);
            if ($cookieJar) {
                $cookieJar->processSetCookieHeaders($request, $tmp);
            }
            if ($tmp->isRedirection()) {
                $location = $tmp->getHeader('Location', "");
                if (!$location) {
                    throw new ClientException("Redirect without a 'Location' header");
                }
                // Resolve redirect - Suprisingly complicated stuff
                if ($location[0] === '/') { // Relative url - Not allowed, but very common.
                    if (strlen($location) > 1 && $location[1] == '/') { // Special case - Protocol relative url
                        // Prepend scheme from previous location
                        $url = parse_url($url, PHP_URL_SCHEME).$location;
                    } else {
                        // Prepend authoritative part from previous location
                        preg_match('~^([a-z]+://[^/]+)/~', $url, $regs);
                        $url = $regs[1].$location;
                    }
                } elseif ($location[0] == '?') { // Relative qs-params
                    $url = preg_replace('~[?].*$~', '', $url).$location;
                } elseif (preg_match('~^[a-z]+://[^/]+/~', $location)) { // Well-formed, absolute url
                    $url = $location;
                } else { // Relative location
                    // Trim off any basename/qs-params from previous location and prepend
                    $url = preg_replace('~/[^/]+$~', '/', $url).$location;
                }
                $newRequest = new Request();
                $newRequest->setMethod('GET'); // Redirects are always GET
                $newRequest->setProtocolVersion($currentRequest->getProtocolVersion());
                $newRequest->fromUrl($url);
                $newRequest->setHeaders($currentRequest->getHeaders());
                if ($cookieJar) {
                    $cookieJar->addCookieHeaders($newRequest);
                }
                $currentRequest = $newRequest;
            } else {
              // We have arrived at our final destination. Load into provided response and exit loop
              $response->setLocation($url);
              $response->setHeaders($responseHeaders);
              $response->setContent($content);
              break;
          }
          $allowedRedirects--;
        }
    }

    private function filterHeaders(array $headers)
    {
        $filtered = array();
        foreach ($headers as $header) {
            if (0 === stripos($header, 'http/')) {
                $filtered = array();
            }

            $filtered[] = $header;
        }

        return $filtered;
    }
}
