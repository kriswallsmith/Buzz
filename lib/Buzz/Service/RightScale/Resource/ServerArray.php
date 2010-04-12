<?php

namespace Buzz\Service\RightScale\Resource;

class ServerArray extends AbstractResource
{
  protected $activeInstancesCount;
  protected $elasticityParams = array();
  protected $serverTemplateHref;
  protected $totalInstancesCount;
  protected $nickname;
  protected $elasticity = array();
  protected $ec2SshKeyHref;
  protected $elasticityStat = array();
  protected $arrayType;
  protected $ec2SecurityGroupsHref = array();
  protected $href;
  protected $description;
  protected $auditQueueHref;
  protected $deploymentHref;
  protected $indicatorHref;
  protected $elasticityFunction;
  protected $active;

  /**
   * @see AbstractResource
   */
  public function fromArray(array $array)
  {
  }
}
