<?php

namespace Buzz\Test\Listener;

use Buzz\Listener\BearerAuthListener;
use Buzz\Message;
use PHPUnit\Framework\TestCase;

class BearerAuthListenerTest extends TestCase
{
    public function testBearerAuthListener()
    {
        $request = new Message\Request();
        $this->assertEmpty($request->getHeader('Authorization'));

        $listener = new BearerAuthListener('superSecretAccessTokenGeneratedByTheNsaItself');
        $listener->preSend($request);

        $this->assertEquals('Bearer superSecretAccessTokenGeneratedByTheNsaItself', $request->getHeader('Authorization'));
    }
}
