<?php

namespace Buzz\Service\RightScale\Resource;

class Status extends AbstractResource
{
  const QUEUED = 'queued';
  const IN_PROGRESS = 'in progress';
  const ABORTED = 'aborted';
  const COMPLETED = 'completed';
  const FAILED = 'failed';

  protected $description;
  protected $startedAt;
  protected $endedAt;
  protected $state;

  /**
   * @see AbstractResource
   */
  public function fromArray(array $array)
  {
    $this->setDescription($array['description']);
    $this->setStartedAt(new \DateTime($array['started_at']));
    $this->setEndedAt(new \DateTime($array['ended_at']));
    $this->setState($array['state']);
  }

  /**
   * Waits for the current status to complete execution.
   * 
   * @param integer $period The number of seconds to wait between checks
   */
  public function wait($period = 1)
  {
    while (!$this->isFinal())
    {
      sleep($period);
      $this->reload();
    }
  }

  /**
   * Returns true if the current status has reached its final state.
   * 
   * @return boolean
   */
  public function isFinal()
  {
    return in_array($this->getState(), array(static::ABORTED, static::COMPLETED, static::FAILED));
  }

  // accessors and mutators

  public function setDescription($description)
  {
    $this->description = $description;
  }

  public function getDescription()
  {
    return $this->description;
  }

  public function setStartedAt(\DateTime $startedAt)
  {
    $this->startedAt = $startedAt;
  }

  public function getStartedAt()
  {
    return $this->startedAt;
  }

  public function setEndedAt(\DateTime $endedAt)
  {
    $this->endedAt = $endedAt;
  }

  public function getEndedAt()
  {
    return $this->endedAt;
  }

  public function setState($state)
  {
    $this->state = $state;
  }

  public function getState()
  {
    return $this->state;
  }
}
