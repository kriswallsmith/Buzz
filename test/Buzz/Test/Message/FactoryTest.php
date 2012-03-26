<?php

namespace Buzz\Test\Message;

use Buzz\Message\Factory\Factory;
use Buzz\Message\Request;
use Buzz\Message\RequestInterface;

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

        $this->assertInstanceOf('Buzz\Message\Request', $request);
        $this->assertEquals(RequestInterface::METHOD_GET, $request->getMethod());
        $this->assertEquals('/', $request->getResource());
        $this->assertNull($request->getHost());
    }

    public function testCreateRequestArguments()
    {
        $request = $this->factory->createRequest(RequestInterface::METHOD_POST, '/foo', 'http://example.com');

        $this->assertEquals(RequestInterface::METHOD_POST, $request->getMethod());
        $this->assertEquals('/foo', $request->getResource());
        $this->assertEquals('http://example.com', $request->getHost());
    }

    public function testCreateFormRequestDefaults()
    {
        $request = $this->factory->createFormRequest();

        $this->assertInstanceOf('Buzz\Message\Form\FormRequest', $request);
        $this->assertEquals(RequestInterface::METHOD_POST, $request->getMethod());
        $this->assertEquals('/', $request->getResource());
        $this->assertNull($request->getHost());
    }

    public function testCreateFormRequestArguments()
    {
        $request = $this->factory->createFormRequest(RequestInterface::METHOD_PUT, '/foo', 'http://example.com');

        $this->assertInstanceOf('Buzz\Message\Form\FormRequest', $request);
        $this->assertEquals(RequestInterface::METHOD_PUT, $request->getMethod());
        $this->assertEquals('/foo', $request->getResource());
        $this->assertEquals('http://example.com', $request->getHost());
    }

    public function testCreateResponse()
    {
        $this->assertInstanceOf('Buzz\Message\Response', $this->factory->createResponse());
    }
}
