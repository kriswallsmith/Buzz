<?php

namespace Buzz\Service\RightScale;

abstract class AbstractResource
{
  protected $api;

  protected $createdAt;
  protected $updatedAt;
  protected $href;

  /**
   * Constructor.
   * 
   * @param API $api A RightScale API object
   */
  public function __construct(API $api)
  {
    $this->setAPI($api);
  }

  /**
   * Populates the current resource from a JSON-serialized data object.
   * 
   * @param string $json A JSON-serialized data object
   */
  public function fromJson($json)
  {
    $this->fromArray(json_decode($json, true));
  }

  // abstract public function fromXml($xml);

  /**
   * Populates the current resource from an array.
   * 
   * @param array $array An array of values for the current object
   */
  abstract public function fromArray(array $array);

  /**
   * Loads the current resource from the supplied URL.
   * 
   * @param string $href The resource URL
   */
  public function load($href)
  {
    $this->setHref($href);
    $this->reload();
  }

  /**
   * Reloads the current resource.
   */
  public function reload()
  {
    $request = new Message\Request('GET');
    $request->fromUrl($this->getHref().'.js');

    $response = new Message\Response();

    $this->getAPI()->send($request, $response);

    $this->fromJson($response->getContent());
  }

  /**
   * Deletes the current resource.
   */
  public function delete()
  {
    $request = new Message\Request('DELETE');
    $request->fromUrl($this->getHref());

    $response = new Message\Response();

    $this->getAPI()->send($request, $response);

    return $response;
  }

  // accessors and mutators

  public function setAPI(API $api)
  {
    $this->api = $api;
  }

  public function getAPI()
  {
    return $this->api;
  }

  public function setCreatedAt(\DateTime $createdAt)
  {
    $this->createdAt = $createdAt;
  }

  public function getCreatedAt()
  {
    return $this->createdAt;
  }

  public function setUpdatedAt(\DateTime $updatedAt)
  {
    $this->updatedAt = $updatedAt;
  }

  public function getUpdatedAt()
  {
    return $this->updatedAt;
  }

  public function setHref($href)
  {
    $this->href = $href;
  }

  public function getHref()
  {
    return $this->href;
  }
}
