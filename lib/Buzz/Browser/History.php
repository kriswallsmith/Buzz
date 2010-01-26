<?php

namespace Buzz\Browser;

use Buzz\Message;

class History
{
  protected $history = array();
  protected $limit = 10;

  public function add(Message\Request $request, Message\Response $response)
  {
    $this->history[] = array($request, $response);
    $this->history = array_slice($this->history, $this->getLimit() * -1);

    end($this->history);
  }

  public function getLast()
  {
    return end($this->history);
  }

  public function setLimit($limit)
  {
    $this->limit = $limit;
  }

  public function getLimit()
  {
    return $this->limit;
  }
}
