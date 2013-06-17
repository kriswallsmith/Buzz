<?php

namespace Buzz\Message;

/**
 * An HTTP response message.
 */
interface ResponseInterface extends MessageInterface
{
    /**
     * Returns the protocol version of the current response.
     *
     * @return float HTTP protocol version
     */
    public function getProtocolVersion();

    /**
     * Returns the status code of the current response.
     *
     * @return integer HTTP status code
     */
    public function getStatusCode();

    /**
     * Returns the reason phrase for the current response.
     *
     * @return string HTTP response reason
     */
    public function getReasonPhrase();
}