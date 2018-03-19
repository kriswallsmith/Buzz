<?php

namespace Buzz\Test\Middleware\History;

use Buzz\Middleware\History\Entry;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;

class EntryTest extends TestCase
{
    public function testDuration()
    {
        $entry = new Entry(new Request('GET', '/'), new Response(), 123);
        $this->assertEquals(123, $entry->getDuration());
    }
}
