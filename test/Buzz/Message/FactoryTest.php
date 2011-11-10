<?php

namespace Buzz\Message;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    private $factory;

    protected function setUp()
    {
        $this->factory = new Factory();
    }

    public function testCreateRequestDefaults()
    {
        $request = $this->factory->createRequest();

        $this->assertEquals(Request::METHOD_GET, $request->getMethod());
        $this->assertEquals('/', $request->getResource());
        $this->assertNull($request->getHost());
    }

    public function testCreateRequestArguments()
    {
        $request = $this->factory->createRequest(Request::METHOD_POST, '/foo', 'http://example.com');

        $this->assertEquals(Request::METHOD_POST, $request->getMethod());
        $this->assertEquals('/foo', $request->getResource());
        $this->assertEquals('http://example.com', $request->getHost());
    }
}
