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
        return array(
            'http' => array(
                // values from the request
                'method'           => $request->getMethod(),
                'header'           => implode("\r\n", $request->getHeaders()),
                'content'          => $request->getContent(),
                'protocol_version' => $request->getProtocolVersion(),

                // values from the current client
                'ignore_errors'    => $this->getIgnoreErrors(),
                'max_redirects'    => $this->getMaxRedirects(),
                'timeout'          => $this->getTimeout(),
            ),
            'ssl' => array(
                'verify_peer'      => $this->getVerifyPeer(),
            ),
        );
    }
}
