<?php

namespace Buzz\Client;

use Buzz\Message;

abstract class AbstractStream extends AbstractClient
{
    /**
     * Converts a request into an array for stream_context_create().
     *
     * @param Message\Request $request A request object
     *
     * @return array An array for stream_context_create()
     */
    public function getStreamContextArray(Message\Request $request)
    {
        switch ($request->getAuthMethod()) {
            case Message\Request::AUTH_METHOD_BASIC:
                $request->addHeader(sprintf('Authorization: Basic %s', base64_encode($request->getAuth())));
                break;
        }
        
        return array('http' => array(
            // values from the request
            'method'           => $request->getMethod(),
            'header'           => implode("\r\n", $request->getHeaders()),
            'content'          => $request->getContent(),
            'protocol_version' => $request->getProtocolVersion(),

            // values from the current client
            'ignore_errors'    => $this->getIgnoreErrors(),
            'max_redirects'    => $this->getMaxRedirects(),
            'timeout'          => $this->getTimeout(),
        ));
    }
}
