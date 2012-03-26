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

        if ($proxyOption = $this->getProxyOption()) {
            $options['http']['proxy'] = $proxyOption;
            $options['http']['request_fulluri'] = true;
        }

        return $options;
    }

    protected function getProxyOption()
    {
        if (!$this->getProxy()) {
            return null;
        }

        if ($user = $this->getProxy()->getUser()) {
            if ($password = $this->getProxy()->getPassword()) {
                $proxyAuth = $user.':'.$password.'@';
            } else {
                $proxyAuth = $user.'@';
            }
        } else {
            $proxyAuth = '';
        }

        // We actually need the port otherwise stream croaks. Had initially
        // tried proxy->getHost() and it did not work since it removed the
        // port it matched the scheme default.
        $proxy = $this->proxy->getHostname() . ':' . $this->proxy->getPort();

        if ('https' === $this->proxy->getScheme()) {
            if (!extension_loaded('openssl')) {
                throw new \RuntimeException('You must enable the openssl extension to use a proxy over https');
            }
            return 'ssl://'.$proxyAuth.$proxy;
        }

        return 'tcp://'.$proxyAuth.$proxy;
    }
}
