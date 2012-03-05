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
        $options = array('http' => array(
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

        if (null !== $proxy = $this->getProxyIp()) {
            if (null !== $proxyAuth = $this->getProxyAuth()) {
                $proxy = $proxyAuth.'@'.$proxy;
            }

            $options['http']['proxy'] = 'tcp://'.$proxy;
            $options['http']['request_fulluri'] = true;
        }

        return $options;
    }
}
