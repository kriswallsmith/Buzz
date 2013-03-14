<?php

namespace Buzz\Listener\History;

use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;

class Journal implements \Countable, \IteratorAggregate
{
    private $entries = array();
    private $limit = 10;

    /**
     * Records an entry in the journal.
     *
     * @param RequestInterface $request  The request
     * @param MessageInterface $response The response
     * @param integer          $duration The duration in seconds
     */
    public function record(RequestInterface $request, MessageInterface $response, $duration = null)
    {
        $this->addEntry(new Entry($request, $response, $duration));
    }

    /**
     * @param Entry $entry
     */
    public function addEntry(Entry $entry)
    {
        array_push($this->entries, $entry);
        $this->entries = array_slice($this->entries, $this->getLimit() * -1);
        end($this->entries);
    }

    /**
     * @return Entry[]
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @return Entry
     */
    public function getLast()
    {
        return end($this->entries);
    }

    /**
     * @return RequestInterface
     */
    public function getLastRequest()
    {
        return $this->getLast()->getRequest();
    }

    /**
     * @return MessageInterface
     */
    public function getLastResponse()
    {
        return $this->getLast()->getResponse();
    }

    /**
     * Clear the entries
     */
    public function clear()
    {
        $this->entries = array();
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->entries);
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator(array_reverse($this->entries));
    }
}
