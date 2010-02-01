<?php

namespace Buzz\Service\RightScale;

use Buzz\Message;

class Server extends AbstractResource
{
  protected $nickname;
  protected $serverType;
  protected $state;
  protected $deploymentHref;
  protected $currentInstanceHref;

  /**
   * @var TagCollection
   */
  protected $tags;

  /**
   * @see AbstractResource
   */
  public function fromArray(array $array)
  {
    $this->setCreatedAt(new \DateTime($array['created_at']));
    $this->setUpdatedAt(new \DateTime($array['updated_at']));
    $this->setNickname($array['nickname']);
    $this->setServerType($array['server_type']);
    $this->setHref($array['href']);
    $this->setState($array['state']);
    $this->setDeploymentHref($array['deployment_href']);
    $this->setCurrentInstanceHref($array['current_instance_href']);

    $tags = new TagCollection($this->getAPI());
    $tags->fromArray($array['tags']);
    $this->setTags($tags);
  }

  // actions

  /**
   * Starts the current server.
   * 
   * @return Message\Response The API response
   */
  public function start()
  {
    $request = new Message\Request('POST');
    $request->fromUrl($this->getHref().'/start');

    $response = new Message\Response();

    $this->getAPI()->send($request, $response);

    return $response;
  }

  /**
   * Stops the current server.
   * 
   * @return Message\Response The API response
   */
  public function stop()
  {
    $request = new Message\Request('POST');
    $request->fromUrl($this->getHref().'/stop');

    $response = new Message\Response();

    $this->getAPI()->send($request, $response);

    return $response;
  }

  /**
   * Reboots the current server.
   * 
   * @return Message\Response The API response
   */
  public function reboot()
  {
    $request = new Message\Request('POST');
    $request->fromUrl($this->getHref().'/reboot');

    $response = new Message\Response();

    $this->getAPI()->send($request, $response);

    return $response;
  }

  /**
   * Runs a RightScript on the current server.
   * 
   * @param RightScript $rightScript  The RightScript to run
   * @param array       $parameters   Parameters for the provided RightScript
   * @param boolean     $asynchronous Whether to skip waiting for the script to complete
   * 
   * @return Message\Response The API response
   */
  public function runScript(RightScript $rightScript, $parameters = array(), $asynchronous = false)
  {
    $request = new Message\Request('POST');
    $request->fromUrl($this->getHref().'/run_script');
    $request->addHeader('Content-Type: x-www-form-urlencoded');
    $request->setContent(http_build_query(array('server' => array(
      'right_script_href' => $rightScript->getHref(),
      'parameters'        => $parameters,
    ))));

    $response = new Message\Response();

    $this->getAPI()->send($request, $response);

    if (!$asynchronous)
    {
      // load the status and wait for it to complete
      $status = new Status($this->getAPI());
      $status->load($response->getHeader('Location'));
      $status->wait();
    }

    return $response;
  }

  /**
   * Attaches a volume to the current server.
   * 
   * @param string $volumeHref The volume's URL
   * @param string $device     The device to attach
   * 
   * @return Message\Response The API response
   */
  public function attachVolume($volumeHref, $device)
  {
    $request = new Message\Request('POST');
    $request->fromUrl($this->getHref().'/attach_volume');
    $request->addHeader('x-www-form-urlencoded');
    $request->setContent(http_build_query(array('server' => array(
      'ec2_ebs_volume_href' => $volumeHref,
      'device'              => $device,
    ))));

    $response = new Message\Response();

    $this->getAPI()->send($request, $response);

    return $response;
  }

  /**
   * Retrieves the current server's settings.
   * 
   * @return Message\Response The API response
   */
  public function getSettings()
  {
    $request = new Message\Request('GET');
    $request->fromUrl($this->getHref().'/settings.js');

    $response = new Message\Response();

    $this->getAPI()->send($request, $response);

    return $response;
  }

  // accessors and mutators

  public function setNickname($nickname)
  {
    $this->nickname = $nickname;
  }

  public function getNickname()
  {
    return $this->nickname;
  }

  public function setServerType($serverType)
  {
    $this->serverType = $serverType;
  }

  public function getServerType()
  {
    return $this->serverType;
  }

  public function setState($state)
  {
    $this->state = $state;
  }

  public function getState()
  {
    return $this->state;
  }

  public function setDeploymentHref($deploymentHref)
  {
    $this->deploymentHref = $deploymentHref;
  }

  public function getDeploymentHref()
  {
    return $this->deploymentHref;
  }

  public function setCurrentInstanceHref($currentInstanceHref)
  {
    $this->currentInstanceHref = $currentInstanceHref;
  }

  public function getCurrentInstanceHref()
  {
    return $this->currentInstanceHref;
  }

  public function setTags(TagCollection $tags)
  {
    $this->tags = $tags;
  }

  public function getTags()
  {
    return $this->tags;
  }
}
