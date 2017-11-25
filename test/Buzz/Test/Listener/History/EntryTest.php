<?php

namespace Buzz\Test\History;

use Buzz\Listener\History\Entry;
use Buzz\Message;
use PHPUnit\Framework\TestCase;

class EntryTest extends TestCase
{
    public function testDuration()
    {
        $entry = new Entry(new Message\Request(), new Message\Response(), 123);
        $this->assertEquals(123, $entry->getDuration());
    }
}
