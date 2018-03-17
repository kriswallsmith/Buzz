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

        $middleware = new BearerAuthListener('superSecretAccessTokenGeneratedByTheNsaItself');
        $middleware->preSend($request);

        $this->assertEquals('Bearer superSecretAccessTokenGeneratedByTheNsaItself', $request->getHeader('Authorization'));
    }
}
