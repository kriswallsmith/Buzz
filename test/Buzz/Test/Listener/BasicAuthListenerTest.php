<?php

namespace Buzz\Test\Listener;

use Buzz\Listener\BasicAuthListener;
use Buzz\Message;
use PHPUnit\Framework\TestCase;

class BasicAuthListenerTest extends TestCase
{
    public function testBasicAuthHeader()
    {
        $request = new Message\Request();
        $this->assertEmpty($request->getHeader('Authorization'));

        $middleware = new BasicAuthListener('foo', 'bar');
        $middleware->preSend($request);

        $this->assertEquals('Basic '.base64_encode('foo:bar'), $request->getHeader('Authorization'));
    }
}
