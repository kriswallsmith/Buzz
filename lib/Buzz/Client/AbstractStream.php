<?php

namespace Buzz\Client;

use Buzz\Message\RequestInterface;

abstract class AbstractStream extends AbstractClient
{
    /**
     * Converts a request into an array for stream_context_create().
     *
     * @param RequestInterface $request A request object
     *
     * @return array An array for stream_context_create()
     */
    public function getStreamContextArray(RequestInterface $request)
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
                'follow_location'  => $this->getMaxRedirects() > 0,
                'max_redirects'    => $this->getMaxRedirects() + 1,
                'timeout'          => $this->getTimeout(),
            ),
            'ssl' => array(
                'verify_peer'      => $this->getVerifyPeer(),
                'verify_host'      => $this->getVerifyHost(),
            ),
        );

        if ($this->proxy) {
            $options['http']['proxy'] = $this->proxy;
            $options['http']['request_fulluri'] = true;
        }

        return $options;
    }
}
