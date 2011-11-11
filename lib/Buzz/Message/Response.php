<?php

namespace Buzz\Message;

class Response extends AbstractMessage
{
    private $protocolVersion;
    private $statusCode;
    private $reasonPhrase;

    /**
     * Returns the protocol version of the current response.
     *
     * @return float
     */
    public function getProtocolVersion()
    {
        if (null === $this->protocolVersion) {
            $this->parseStatusLine();
        }

        return $this->protocolVersion ?: null;
    }

    /**
     * Returns the status code of the current response.
     *
     * @return integer
     */
    public function getStatusCode()
    {
        if (null === $this->statusCode) {
            $this->parseStatusLine();
        }

        return $this->statusCode ?: null;
    }

    /**
     * Returns the reason phrase for the current response.
     *
     * @return string
     */
    public function getReasonPhrase()
    {
        if (null === $this->reasonPhrase) {
            $this->parseStatusLine();
        }

        return $this->reasonPhrase ?: null;
    }

    public function setHeaders(array $headers)
    {
        parent::setHeaders($headers);

        $this->resetStatusLine();
    }

    public function addHeader($header)
    {
        parent::addHeader($header);

        $this->resetStatusLine();
    }

    public function addHeaders(array $headers)
    {
        parent::addHeaders($headers);

        $this->resetStatusLine();
    }

    public function fromString($raw)
    {
        $lines = preg_split('/(\\r?\\n)/', $raw, -1, PREG_SPLIT_DELIM_CAPTURE);
        for ($i = 0, $count = count($lines); $i < $count; $i += 2) {
            $line = $lines[$i];
            $eol = isset($lines[$i + 1]) ? $lines[$i + 1] : '';

            if (empty($line)) {
                $this->setContent(implode('', array_slice($lines, $i + 2)));
                break;
            } else {
                $this->addHeader($line);
            }
        }
    }

    // private

    private function parseStatusLine()
    {
        $headers = $this->getHeaders();

        if (isset($headers[0]) && 3 == count($parts = explode(' ', $headers[0], 3))) {
            $this->protocolVersion = (float) $parts[0];
            $this->statusCode = (integer) $parts[1];
            $this->reasonPhrase = $parts[2];
        } else {
            $this->protocolVersion = $this->statusCode = $this->reasonPhrase = false;
        }
    }

    private function resetStatusLine()
    {
        $this->protocolVersion = $this->statusCode = $this->reasonPhrase = null;
    }
}
