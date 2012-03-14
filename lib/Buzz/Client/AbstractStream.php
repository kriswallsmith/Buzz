<?php

namespace Buzz\Client;

use Buzz\Message;

abstract class AbstractStream extends AbstractClient
{
    protected function getProxyOption()
    {
        if ($proxyAuth = $this->getProxyAuth()) {
            $proxyAuth .= '@';
        }

        $proxy = $this->getProxy();

        if (0 === strpos($proxy, 'http://')) {
            return str_replace('http://', 'tcp://'.$proxyAuth, $proxy);
        } elseif (0 === strpos($proxy, 'https://')) {
            if (!extension_loaded('openssl')) {
                throw new \RuntimeException('You must enable the openssl extension to use a proxy over https');
            }
            return str_replace('https://', 'ssl://'.$proxyAuth, $proxy);
        } else {
            return 'tcp://'.$proxyAuth.$proxy;
        }
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
        $options = array(
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
        if ($this->getProxyEnabled()) {
            $options['http']['proxy'] = $this->getProxyOption();
            $options['http']['request_fulluri'] = true;
        }
        return $options;
    }
}
