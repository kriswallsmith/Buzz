<?php

namespace Buzz\Client;

use Buzz\Message\RequestInterface;

abstract class AbstractStream extends AbstractClient
{
    /**
     * Converts a request into an array for stream_context_create().
     *
     * @param RequestInterface $request A request object
     * @param integer $maxRedirects     Optional override for max-redirects option. If not supplied, use `$this->getMaxRedirects()`
     *
     * @return array An array for stream_context_create()
     */
    public function getStreamContextArray(RequestInterface $request, $maxRedirects = null)
    {
        $options = array(
            'http' => array(
                // values from the request
                'method'           => $request->getMethod(),
                'header'           => implode("\r\n", $request->getHeaders()),
                'content'          => $request->getContent(),
                'protocol_version' => $request->getProtocolVersion(),

                // values from the current client
                'ignore_errors'    => $this->getIgnoreErrors(),
                'max_redirects'    => is_null($maxRedirects) ? $this->getMaxRedirects() : $maxRedirects,
                'timeout'          => $this->getTimeout(),
            ),
            'ssl' => array(
                'verify_peer'      => $this->getVerifyPeer(),
            ),
        );

        if ($this->proxy) {
            $options['http']['proxy'] = $this->proxy;
            $options['http']['request_fulluri'] = true;
        }

        return $options;
    }
}
