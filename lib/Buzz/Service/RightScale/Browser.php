<?php

namespace Buzz\Service\RightScale;

use Buzz;
use Buzz\Client;
use Buzz\History;
use Buzz\Message;
use Buzz\Service\RightScale\Resource;

class Browser extends Buzz\Browser
{
  const HOST = 'https://my.rightscale.com';

  protected $accountId;
  protected $username;
  protected $password;

  public function __construct($accountId = null, $username = null, $password = null, Client\ClientInterface $client = null, History\Journal $journal = null)
  {
    $this->setAccountId($accountId);
    $this->setUsername($username);
    $this->setPassword($password);

    parent::__construct($client, $journal);
  }

  /**
   * Returns true if both a username and password have been entered.
   * 
   * @return boolean True if the current browser has credentials
   */
  public function hasCredentials()
  {
    return null !== $this->getUsername() && null != $this->getPassword();
  }

  // deployments

  /**
   * Returns all deployments on the current account.
   * 
   * @return DeploymentCollection A collection of deployments
   */
  public function getDeployments()
  {
    $response = $this->get('/api/acct/'.$this->getAccountId().'/deployments.js');

    $deployments = new Resource\DeploymentCollection();
    $deployments->fromJson($response->getContent());

    return $deployments;
  }

  /**
   * Finds deployments by nickname.
   * 
   * @param string $nickname A nickname or regular expression
   * 
   * @return DeploymentCollection A collection of matching deployments
   */
  public function findDeploymentsByNickname($nickname, $limit = null)
  {
    $deployments = new Resource\DeploymentCollection();

    // choose a comparision function
    if (preg_match('/^(!)?([^a-zA-Z0-9\\\\]).+?\\2[ims]?$/', $nickname, $match))
    {
      if ('!' == $match[1])
      {
        $compare = function ($nickname, $value) { return !preg_match(substr($nickname, 1), $value); };
      }
      else
      {
        $compare = function ($nickname, $value) { return preg_match($nickname, $value); };
      }
    }
    else
    {
      $compare = function ($nickname, $value) { return $nickname == $value; };
    }

    foreach ($this->getDeployments() as $deployment)
    {
      if (null !== $limit && $limit <= count($deployments))
      {
        break;
      }

      if ($compare($nickname, $deployment->getNickname()))
      {
        $deployments->addDeployment($deployment);
      }
    }

    return $deployments;
  }

  /**
   * Finds a deployment with a certain nickname.
   * 
   * @param string $nickname A nickname or regular expression
   * 
   * @return Deployment|null The deployment, if found
   */
  public function findDeploymentByNickname($nickname)
  {
    return current($this->findDeploymentsByNickname($nickname, 1)) ?: null;
  }

  // rightscripts

  /**
   * Returns all RightScripts on the current account.
   * 
   * @return RightScriptCollection
   */
  public function getRightScripts()
  {
    $response = $this->get('/api/acct/'.$this->getAccountId().'/right_scripts.xml');

    $rightScripts = new Resource\RightScriptCollection();
    $rightScripts->fromXml($response->getContent());

    return $rightScripts;
  }

  /**
   * Finds RightScripts by name.
   * 
   * @param string $name A name or regular expression
   * 
   * @return RightScriptCollection A collection of matching RightScripts
   */
  public function findRightScriptsByName($name, $limit = null)
  {
    $rightScripts = array();

    // choose a comparision function
    if (preg_match('/^(!)?([^a-zA-Z0-9\\\\]).+?\\2[ims]?$/', $name, $match))
    {
      if ('!' == $match[1])
      {
        $compare = function ($name, $value) { return !preg_match(substr($name, 1), $value); };
      }
      else
      {
        $compare = function ($name, $value) { return preg_match($name, $value); };
      }
    }
    else
    {
      $compare = function ($name, $value) { return $name == $value; };
    }

    foreach ($this->getRightScripts() as $rightScript)
    {
      if (null !== $limit && $limit <= count($rightScripts))
      {
        break;
      }

      if ($compare($name, $rightScript->getName()))
      {
        $rightScripts[] = $rightScript;
      }
    }

    return $rightScripts;
  }

  /**
   * Finds a RightScript with a certain name.
   * 
   * @param string $name A name or regular expression
   * 
   * @return RightScript|null The RightScript, if found
   */
  public function findRightScriptByName($name)
  {
    return current($this->findRightScriptsByName($name, 1)) ?: null;
  }

  // servers

  /**
   * Returns all servers on the current account.
   * 
   * @return ServerCollection
   */
  public function getServers()
  {
    $response = $this->get('/api/acct/'.$this->getAccountId().'/servers.js');

    $servers = new Resource\ServerCollection();
    $servers->fromJson($response->getContent());

    return $servers;
  }

  /**
   * Finds servers by nickname.
   * 
   * @param string $nickname A nickname or regular expression
   * 
   * @return ServerCollection A collection of matching servers
   */
  public function findServersByNickname($nickname, $limit = null)
  {
    $servers = new Resource\ServerCollection();

    // choose a comparision function
    if (preg_match('/^(!)?([^a-zA-Z0-9\\\\]).+?\\2[ims]?$/', $nickname, $match))
    {
      if ('!' == $match[1])
      {
        $compare = function ($nickname, $value) { return !preg_match(substr($nickname, 1), $value); };
      }
      else
      {
        $compare = function ($nickname, $value) { return preg_match($nickname, $value); };
      }
    }
    else
    {
      $compare = function ($nickname, $value) { return $nickname == $value; };
    }

    foreach ($this->getServers() as $server)
    {
      if (null !== $limit && $limit <= count($servers))
      {
        break;
      }

      if ($compare($nickname, $server->getNickname()))
      {
        $servers->addServer($server);
      }
    }

    return $servers;
  }

  /**
   * Finds a server with a certain nickname.
   * 
   * @param string $nickname A nickname or regular expression
   * 
   * @return Server|null The server, if found
   */
  public function findServerByNickname($nickname)
  {
    return current($this->findServersByNickname($nickname, 1)) ?: null;
  }

  // server arrays

  /**
   * Returns all server arrays on the current account.
   * 
   * @return ServerArrayCollection
   */
  public function getServerArrays()
  {
    $response = $this->get('/api/acct/'.$this->getAccountId().'/server_arrays.js');

    $serverArrays = new Resource\ServerArrayCollection();
    $serverArrays->fromJson($response->getContent());

    return $serverArrays;
  }

  /**
   * Finds server arrays by nickname.
   * 
   * @param string $nickname A nickname or regular expression
   * 
   * @return ServerArrayCollection A collection of matching server arrays
   */
  public function findServerArraysByNickname($nickname, $limit = null)
  {
    $serverArrays = new Resource\ServerArrayCollection();

    // choose a comparision function
    if (preg_match('/^(!)?([^a-zA-Z0-9\\\\]).+?\\2[ims]?$/', $nickname, $match))
    {
      if ('!' == $match[1])
      {
        $compare = function ($nickname, $value) { return !preg_match(substr($nickname, 1), $value); };
      }
      else
      {
        $compare = function ($nickname, $value) { return preg_match($nickname, $value); };
      }
    }
    else
    {
      $compare = function ($nickname, $value) { return $nickname == $value; };
    }

    foreach ($this->getServerArrays() as $serverArray)
    {
      if (null !== $limit && $limit <= count($serverArrays))
      {
        break;
      }

      if ($compare($nickname, $serverArray->getNickname()))
      {
        $serverArrays->addServerArray($serverArray);
      }
    }

    return $serverArrays;
  }

  /**
   * Finds a server array with a certain nickname.
   * 
   * @param string $nickname A nickname or regular expression
   * 
   * @return ServerArray|null The server array, if found
   */
  public function findServerArrayByNickname($nickname)
  {
    return current($this->findServerArraysByNickname($nickname, 1)) ?: null;
  }

  // accessors and mutators

  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }

  public function getAccountId()
  {
    return $this->accountId;
  }

  public function setUsername($username)
  {
    $this->username = $username;
  }

  public function getUsername()
  {
    return $this->username;
  }

  public function setPassword($password)
  {
    $this->password = $password;
  }

  public function getPassword()
  {
    return $this->password;
  }

  /**
   * @see Buzz\Browser
   */
  public function send(Message\Request $request, Message\Response $response = null)
  {
    $this->prepareRequest($request);

    return parent::send($request, $response);
  }

  public function prepareRequest(Message\Request $request)
  {
    // add account id to the request resource
    if (false !== strpos($request->getResource(), '%s'))
    {
      $request->setResource(sprintf($request->getResource(), $this->getAccountId()));
    }

    // add the rightscale host
    if (null === $request->getHost())
    {
      $request->setHost(static::HOST);
    }

    if ($this->hasCredentials())
    {
      $request->addHeader('Authorization: Basic '.base64_encode($this->getUsername().':'.$this->getPassword()));
    }

    $request->addHeader('X-API-VERSION: 1.0');
  }
}
