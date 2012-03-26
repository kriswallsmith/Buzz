<?php

namespace Buzz\Test\History;

use Buzz\Listener\History\Entry;
use Buzz\Message;

class EntryTest extends \PHPUnit_Framework_TestCase
{
    public function testDuration()
    {
        $entry = new Entry(new Message\Request(), new Message\Response(), 123);
        $this->assertEquals(123, $entry->getDuration());
    }
}
