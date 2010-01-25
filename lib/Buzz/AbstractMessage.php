<?php

namespace Buzz;

abstract class AbstractMessage
{
  protected $headers = array();
  protected $content;

  /**
   * Returns the value of a header.
   * 
   * @param string         $name
   * @param string|boolean $glue Glue for implode, or false to return an array
   * 
   * @return string|array|null
   */
  public function getHeader($name, $glue = PHP_EOL)
  {
    $needle = $name.':';

    $values = array();
    foreach ($this->getHeaders() as $header)
    {
      if (0 === strpos($header, $needle))
      {
        $values[] = trim(substr($header, strlen($needle)));;
      }
    }

    if (false === $glue)
    {
      return $values;
    }
    else
    {
      return count($values) ? implode($glue, $values) : null;
    }
  }

  public function clearHeaders()
  {
    $this->setHeaders(array());
  }

  public function setHeaders(array $headers)
  {
    $this->headers = $headers;
  }

  public function addHeader($header)
  {
    $this->headers[] = $header;
  }

  public function addHeaders(array $headers)
  {
    $this->headers = array_merge($this->headers, $headers);
  }

  public function getHeaders()
  {
    return $this->headers;
  }

  public function setContent($content)
  {
    $this->content = $content;
  }

  public function getContent()
  {
    return $this->content;
  }

  public function __toString()
  {
    $string = implode(PHP_EOL, $this->getHeaders()).PHP_EOL;

    if ($this->getContent())
    {
      $string .= PHP_EOL.$this->getContent().PHP_EOL;
    }

    return $string;
  }
}
