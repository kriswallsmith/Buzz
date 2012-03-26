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
     * Returns an array of header lines.
     *
     * @return array An array of headers (integer indexes)
     */
    function getHeaders();

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
}
