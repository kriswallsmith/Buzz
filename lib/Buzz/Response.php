<?php

namespace Buzz;

class Response extends AbstractMessage
{
  /**
   * Returns the protocol version of the current response.
   * 
   * @return float
   */
  public function getProtocolVersion()
  {
    if (isset($this->headers[0]))
    {
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
    if (isset($this->headers[0]))
    {
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
    if (isset($this->headers[0]))
    {
      list($httpVersion, $statusCode, $reasonPhrase) = explode(' ', $this->headers[0]);

      return $reasonPhrase;
    }
  }
}
