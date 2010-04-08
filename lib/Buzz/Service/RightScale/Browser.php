<?php

namespace Buzz\Service\RightScale;

use Buzz;
use Buzz\Message;

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
    $response = $this->get(static::HOST.'/api/acct/'.$this->getAccountId().'/deployments.js');

    $deployments = new DeploymentCollection();
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
    $deployments = new DeploymentCollection();

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
    $deployments = $this->findDeploymentsByNickname($nickname, 1);

    return count($deployments) ? $deployments->getDeployment(0) : null;
  }

  // rightscripts

  /**
   * Returns all RightScripts on the current account.
   * 
   * @return array An array of RightScript objects
   */
  public function getRightScripts()
  {
    $response = $this->get(static::HOST.'/api/acct/'.$this->getAccountId().'/right_scripts.xml');

    $rightScripts = new RightScriptCollection();
    $rightScripts->fromXml($response->getContent());

    return $rightScripts;
  }

  /**
   * Finds RightScripts by name.
   * 
   * @param string $name A name or regular expression
   * 
   * @return array An array of matching RightScripts
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
    $rightScripts = $this->findRightScriptsByName($name, 1);

    return count($rightScripts) ? $rightScripts[0] : null;
  }

  // todo: servers
  // todo: server arrays

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
  public function getNewRequest($url, $method, $headers = array())
  {
    $request = parent::getNewRequest($url, $method, $headers);

    if ($this->hasCredentials())
    {
      $request->addHeader('Authorization: Basic '.base64_encode($this->getUsername().':'.$this->getPassword()));
    }

    $request->addHeader('X-API-VERSION: 1.0');

    return $request;
  }
}
