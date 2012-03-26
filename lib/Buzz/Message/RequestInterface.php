<?php

namespace Buzz\Message;

/**
 * An HTTP request message.
 *
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 */
interface RequestInterface extends MessageInterface
{
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_GET     = 'GET';
    const METHOD_HEAD    = 'HEAD';
    const METHOD_POST    = 'POST';
    const METHOD_PUT     = 'PUT';
    const METHOD_DELETE  = 'DELETE';
    const METHOD_PATCH   = 'PATCH';

    /**
     * Returns the HTTP method of the current request.
     *
     * @return string An HTTP method
     */
    function getMethod();

    /**
     * Sets the HTTP method of the current request.
     *
     * @param string $method The request method
     */
    function setMethod($method);

    /**
     * Returns the resource portion of the request line.
     *
     * @return string The resource requested
     */
    function getResource();

    /**
     * Sets the resource for the current request.
     *
     * @param string $resource The resource being requested
     */
    function setResource($resource);

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
     * Sets the host for the current request.
     *
     * @param string $host The host
     */
    function setHost($host);

    /**
     * Checks if the current request is secure.
     *
     * @return Boolean True if the request is secure
     */
    function isSecure();
}
