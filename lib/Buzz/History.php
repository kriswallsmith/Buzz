<?php

namespace Buzz;

class History
{
  protected $history = array();
  protected $limit = 10;

  public function add(Request $request, Response $response)
  {
    $this->history[] = array($request, $response);
    $this->history = array_slice($this->history, $this->getLimit() * -1);

    end($this->history);
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
