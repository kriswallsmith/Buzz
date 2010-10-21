<?php

namespace Buzz\Message;

class Response extends AbstractMessage
{
    /**
     * Returns the protocol version of the current response.
     * 
     * @return float
     */
    public function getProtocolVersion()
    {
        if (isset($this->headers[0])) {
            list($httpVersion, $statusCode, $reasonPhrase) = explode(' ', $this->headers[0]);

            return (float) $httpVersion;
        }
    }

    /**
     * Returns the status code of the current response.
     * 
     * @return integer
     */
    public function getStatusCode()
    {
        if (isset($this->headers[0])) {
            list($httpVersion, $statusCode, $reasonPhrase) = explode(' ', $this->headers[0]);

            return (integer) $statusCode;
        }
    }

    /**
     * Returns the reason phrase for the current response.
     * 
     * @return string
     */
    public function getReasonPhrase()
    {
        if (isset($this->headers[0])) {
            list($httpVersion, $statusCode, $reasonPhrase) = explode(' ', $this->headers[0]);

            return $reasonPhrase;
        }
    }

    public function fromString($raw)
    {
        $lines = preg_split('/(\\r?\\n)/', $raw, -1, PREG_SPLIT_DELIM_CAPTURE);
        for ($i = 0; $i < count($lines); $i += 2) {
            $line = $lines[$i];
            $eol = $lines[$i + 1];

            if (empty($line)) {
                $this->setContent(implode('', array_slice($lines, $i + 2)));
                break;
            } else {
                $this->addHeader($line);
            }
        }
    }
}
