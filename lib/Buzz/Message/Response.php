<?php

namespace Buzz\Message;

class Response extends AbstractMessage
{
    protected $httpVersion;
    protected $statusCode;
    protected $reasonPhrase;

    /**
     * Returns the protocol version of the current response.
     * 
     * @return float
     */
    public function getProtocolVersion()
    {
        $this->parseStatusHeader();

        return $this->httpVersion;
    }

    /**
     * Returns the status code of the current response.
     * 
     * @return integer
     */
    public function getStatusCode()
    {
        $this->parseStatusHeader();

        return $this->statusCode;
    }

    /**
     * Returns the reason phrase for the current response.
     * 
     * @return string
     */
    public function getReasonPhrase()
    {
        $this->parseStatusHeader();

        return $this->reasonPhrase;
    }

    public function fromString($raw)
    {
        $lines = preg_split('/(\\r?\\n)/', $raw, -1, PREG_SPLIT_DELIM_CAPTURE);
        for ($i = 0, $count = count($lines); $i < $count; $i += 2) {
            $line = $lines[$i];

            if (empty($line)) {
                $this->setContent(implode('', array_slice($lines, $i + 2)));
                break;
            }

            $this->addHeader($line);
        }
    }

    protected function parseStatusHeader()
    {
        if (!isset($this->httpVersion, $this->statusCode, $this->reasonPhrase) && isset($this->headers[0])) {
            list($httpVersion, $statusCode, $reasonPhrase) = explode(' ', $this->headers[0], 3);

            $this->httpVersion  = (float) $httpVersion;
            $this->statusCode   = (integer) $statusCode;
            $this->reasonPhrase = $reasonPhrase;
        }
    }
}