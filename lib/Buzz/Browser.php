<?php

namespace Buzz;

use Buzz\Client;
use Buzz\Browser;
use Buzz\Message;

class Browser
{
  protected $client;
  protected $history;
  protected $requestFactory;
  protected $responseFactory;
  protected $services = array();
  protected $serviceWrapper;

  public function __construct(Client\ClientInterface $client = null, Browser\History $history = null)
  {
    $this->setClient($client ?: new Client\FileGetContents());
    $this->setHistory($history ?: new Browser\History());
  }

  public function get($url, $headers = array())
  {
    return $this->call($url, Message\Request::METHOD_GET, $headers);
  }

  public function post($url, $headers = array())
  {
    return $this->call($url, Message\Request::METHOD_POST, $headers);
  }

  public function head($url, $headers = array())
  {
    return $this->call($url, Message\Request::METHOD_HEAD, $headers);
  }

  public function put($url, $headers = array())
  {
    return $this->call($url, Message\Request::METHOD_PUT, $headers);
  }

  public function delete($url, $headers = array())
  {
    return $this->call($url, Message\Request::METHOD_DELETE, $headers);
  }

  /**
   * Sends a request and adds the call to the history.
   * 
   * @param string $url     The URL to call
   * @param string $method  The request method to use
   * @param array  $headers An array of request headers
   * 
   * @return Response The response object
   */
  public function call($url, $method, $headers = array())
  {
    $request = $this->getNewRequest($url, $method, $headers);
    $response = $this->getNewResponse();

    $this->getClient()->send($request, $response);

    $this->getHistory()->add($request, $response);

    return $response;
  }

  /**
   * Returns a DOMDocument for the current response.
   * 
   * @return DOMDocument
   */
  public function getDom()
  {
    list($request, $response) = $this->getHistory()->getLast();

    return $response->toDomDocument();
  }

  /**
   * Registers a service to the current browser.
   * 
   * @param Service\ServiceInterface $service A service object
   * @param string                   $name    Use this name for accessing the supplied service
   */
  public function registerService(Service\ServiceInterface $service, $name = null)
  {
    $this->services[$name ?: $services->getName()] = $service;
  }

  /**
   * Returns a registered service.
   * 
   * @param string $name The registered service name
   * 
   * @return Service\ServiceInterface A service object
   */
  public function getService($name)
  {
    if (isset($this->services[$name]))
    {
      return $this->services[$name];
    }
  }

  /**
   * Moves into a fluent interface with the supplied service.
   * 
   * @param string $service A service name
   * 
   * @return ServiceWrapper The service wrapped in a fluent interface for the current browser
   */
  public function with($service)
  {
    if (!$this->serviceWrapper)
    {
      $this->serviceWrapper = new Browser\ServiceWrapper($this);
    }

    $this->serviceWrapper->setService($this->getService($service));

    return $this->serviceWrapper;
  }

  public function setClient(Client\ClientInterface $client)
  {
    $this->client = $client;
  }

  public function getClient()
  {
    return $this->client;
  }

  public function setHistory(Browser\History $history)
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

    $request = new Message\Request($method);
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

    return new Message\Response();
  }
}
