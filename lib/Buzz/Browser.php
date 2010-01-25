<?php

namespace Buzz;

class Browser
{
  protected $client;
  protected $history;
  protected $requestFactory;
  protected $responseFactory;

  public function __construct(ClientInterface $client = null, History $history = null)
  {
    $this->setClient($client ?: new FileGetContentsClient());
    $this->setHistory($history ?: new History());
  }

  public function get($url, $headers = array())
  {
    $this->call($url, Request::METHOD_GET, $headers);
  }

  public function post($url, $headers = array())
  {
    $this->call($url, Request::METHOD_POST, $headers);
  }

  public function head($url, $headers = array())
  {
    $this->call($url, Request::METHOD_HEAD, $headers);
  }

  public function put($url, $headers = array())
  {
    $this->call($url, Request::METHOD_PUT, $headers);
  }

  public function delete($url, $headers = array())
  {
    $this->call($url, Request::METHOD_DELETE, $headers);
  }

  /**
   * Sends a request and adds the call to the history.
   * 
   * @param string $url     The URL to call
   * @param string $method  The request method to use
   * @param array  $headers An array of request headers
   */
  public function call($url, $method, $headers = array())
  {
    $request = $this->getNewRequest($url, $method, $headers);
    $response = $this->getNewResponse();

    $this->getClient()->send($request, $response);

    $this->history->add($request, $response);
  }

  public function setClient(ClientInterface $client)
  {
    $this->client = $client;
  }

  public function getClient()
  {
    return $this->client;
  }

  public function setHistory(BrowserHistory $history)
  {
    $this->history = $history;
  }

  public function getHistory()
  {
    return $this->history;
  }

  public function setRequestFactory($callable)
  {
    $this->requestFactory = $callable;
  }

  public function getRequestFactory()
  {
    return $this->requestFactory;
  }

  public function setResponseFactory($callable)
  {
    $this->responseFactory = $callable;
  }

  public function getResponseFactory()
  {
    return $this->responseFactory;
  }

  public function getNewRequest($url, $method, $headers = array())
  {
    if ($callable = $this->getRequestFactory())
    {
      return $callable($url, $method, $headers);
    }

    $request = new Request($method);
    $request->fromUrl($url);
    $request->addHeaders($headers);

    return $request;
  }

  public function getNewResponse()
  {
    if ($callable = $this->getResponseFactory())
    {
      return $callable();
    }

    return new Response();
  }
}
