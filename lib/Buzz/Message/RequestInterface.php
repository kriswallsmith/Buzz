<?php

namespace Buzz\Message;

/**
 * An HTTP request message.
 *
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 */
interface RequestInterface extends MessageInterface
{
    /**
     * Returns the HTTP method of the current request.
     *
     * @return string An HTTP method
     */
    function getMethod();

    /**
     * Returns the resource portion of the request line.
     *
     * @return string The resource requested
     */
    function getResource();

    /**
     * Returns the protocol version of the current request.
     *
     * @return float The protocol version
     */
    function getProtocolVersion();

    /**
     * Returns the value of the host header.
     *
     * @return string|null The host
     */
    function getHost();

    /**
     * Checks if the current request is secure.
     *
     * @return Boolean True if the request is secure
     */
    function isSecure();
}
