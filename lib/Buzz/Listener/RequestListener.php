<?php
namespace Practo\ApiBundle\Listener;
use Buzz\Listener\ListenerInterface;
use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;
/**
 * Intercepts calls made using Buzz.
 */
class RequestListener implements ListenerInterface
{
    /**
     * Intercept the outgoing request from Buzz
     * - Adding 'Cid' to the outgoing request headers
     *
     * @param RequestInterface $request
     */
    public function preSend(RequestInterface $request)
    {
        $cid = '';
        if (array_key_exists('Cid', $_REQUEST)) {
            $cid = $_REQUEST['Cid'];
        }
        $request->addHeader('Cid: ' . $cid);
    }
    /**
     * Intercept the response received for the above request
     *
     * @param RequestInterface $request
     * @param MessageInterface $response
     */
    public function postSend(RequestInterface $request, MessageInterface $response)
    {
        // Not required as of now
    }
}
