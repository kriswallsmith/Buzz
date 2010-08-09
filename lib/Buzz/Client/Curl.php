<?php

namespace Buzz\Client;

use Buzz\Message;

class Curl implements ClientInterface
{
  protected $curl;

  public function __construct()
  {
    $this->curl = curl_init();

    curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->curl, CURLOPT_HEADER, true);
  }

  public function getCurl()
  {
    return $this->curl;
  }

  public function send(Message\Request $request, Message\Response $response)
  {
    curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $request->getMethod());
    curl_setopt($this->curl, CURLOPT_URL, $request->getUrl());
    curl_setopt($this->curl, CURLOPT_HTTPHEADER, $request->getHeaders());
    curl_setopt($this->curl, CURLOPT_POSTFIELDS, $request->getContent());

    $raw = curl_exec($this->curl);

    $lines = preg_split('/(\\r?\\n)/', $raw, -1, PREG_SPLIT_DELIM_CAPTURE);
    for ($i = 0; $i < count($lines); $i += 2)
    {
      $line = $lines[$i];
      $eol = $lines[$i + 1];

      if (empty($line))
      {
        $response->setContent(implode('', array_slice($lines, $i + 2)));
        break;
      }
      else
      {
        $response->addHeader($line);
      }
    }
  }

  public function __destruct()
  {
    curl_close($this->curl);
  }
}
