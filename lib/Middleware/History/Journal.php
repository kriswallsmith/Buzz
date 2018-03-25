<?php

declare(strict_types=1);

namespace Buzz\Middleware\History;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Journal implements \Countable, \IteratorAggregate
{
    private $entries = [];
    private $limit = 10;

    public function __construct(int $limit = 10)
    {
        $this->limit = $limit;
    }

    /**
     * Records an entry in the journal.
     *
     * @param RequestInterface  $request  The request
     * @param ResponseInterface $response The response
     * @param null|int          $duration The duration in seconds
     */
    public function record(RequestInterface $request, ResponseInterface $response, int $duration = null): void
    {
        $this->addEntry(new Entry($request, $response, $duration));
    }

    public function addEntry(Entry $entry): void
    {
        array_push($this->entries, $entry);
        $this->entries = array_slice($this->entries, $this->getLimit() * -1);
        end($this->entries);
    }

    /**
     * @return Entry[]
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    public function getLast(): ?Entry
    {
        $entry = end($this->entries);

        return false === $entry ? null : $entry;
    }

    public function getLastRequest(): ?RequestInterface
    {
        if (null === $entry = $this->getLast()) {
            return null;
        }

        return $entry->getRequest();
    }

    public function getLastResponse(): ?ResponseInterface
    {
        if (null === $entry = $this->getLast()) {
            return null;
        }

        return $entry->getResponse();
    }

    public function clear(): void
    {
        $this->entries = [];
    }

    public function count(): int
    {
        return count($this->entries);
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator(array_reverse($this->entries));
    }
}
