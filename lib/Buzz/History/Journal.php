<?php

namespace Buzz\History;

use Buzz\Message;

class Journal implements \Countable, \IteratorAggregate
{
    protected $entries = array();
    protected $limit = 10;

    public function record(Message\Request $request, Message\Response $response)
    {
        $this->addEntry(new Entry($request, $response));
    }

    public function addEntry(Entry $entry)
    {
        array_push($this->entries, $entry);
        $this->entries = array_slice($this->entries, $this->getLimit() * -1);
        end($this->entries);
    }

    public function getLast()
    {
        return end($this->entries);
    }

    public function getLastRequest()
    {
        return $this->getLast()->getRequest();
    }

    public function getLastResponse()
    {
        return $this->getLast()->getResponse();
    }

    public function clear()
    {
        $this->entries = array();
    }

    public function count()
    {
        return count($this->entries);
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getIterator()
    {
        return new \ArrayIterator(array_reverse($this->entries));
    }
}
