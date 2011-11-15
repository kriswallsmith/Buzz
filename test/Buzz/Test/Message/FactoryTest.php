<?php

namespace Buzz\Test\Message;

use Buzz\Message\Factory;
use Buzz\Message\Request;

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

        $this->assertInstanceOf('Buzz\\Message\\Request', $request);
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

    public function testCreateFormRequestDefaults()
    {
        $request = $this->factory->createFormRequest();

        $this->assertInstanceOf('Buzz\\Message\\FormRequest', $request);
        $this->assertEquals(Request::METHOD_POST, $request->getMethod());
        $this->assertEquals('/', $request->getResource());
        $this->assertNull($request->getHost());
    }

    public function testCreateFormRequestArguments()
    {
        $request = $this->factory->createFormRequest(Request::METHOD_PUT, '/foo', 'http://example.com');

        $this->assertInstanceOf('Buzz\\Message\\FormRequest', $request);
        $this->assertEquals(Request::METHOD_PUT, $request->getMethod());
        $this->assertEquals('/foo', $request->getResource());
        $this->assertEquals('http://example.com', $request->getHost());
    }

    public function testCreateResponse()
    {
        $this->assertInstanceOf('Buzz\\Message\\Response', $this->factory->createResponse());
    }
}
