<?php

namespace Buzz\Message;

/**
 * An HTTP message.
 *
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 */
interface MessageInterface
{
    /**
     * Returns a header value.
     *
     * @param string         $name A header name
     * @param string|boolean $glue Glue for implode, or false to return an array
     *
     * @return string|array|null The header value(s)
     */
    function getHeader($name, $glue = "\r\n");

    /**
     * Returns an array of header lines.
     *
     * @return array An array of header lines (integer indexes)
     */
    function getHeaders();

    /**
     * Sets all headers on the current message.
     *
     * @param array $headers An array of header lines
     */
    function setHeaders(array $headers);

    /**
     * Adds a header to this message.
     *
     * @param string $header A header line
     */
    function addHeader($header);

    /**
     * Returns the content of the message.
     *
     * @return string The message content
     */
    function getContent();

    /**
     * Sets the content of the message.
     *
     * @param string $content The message content
     */
    function setContent($content);

    /**
     * Returns the message document.
     *
     * @return string The message
     */
    function __toString();
}
