<?php

namespace Buzz\Client;

use Buzz\Message;

abstract class AbstractStream
{
    protected $ignoreErrors = true;
    protected $maxRedirects = 5;
    protected $timeout = 5;

    public function setIgnoreErrors($ignoreErrors)
    {
        $this->ignoreErrors = $ignoreErrors;
    }

    public function getIgnoreErrors()
    {
        return $this->ignoreErrors;
    }

    public function setMaxRedirects($maxRedirects)
    {
        $this->maxRedirects = $maxRedirects;
    }

    public function getMaxRedirects()
    {
        return $this->maxRedirects;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Converts a request into an array for stream_context_create().
     * 
     * @param Message\Request $request A request object
     * 
     * @return array An array for stream_context_create()
     */
    public function getStreamContextArray(Message\Request $request)
    {
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
