<?php

namespace Buzz\Test;

use Buzz\Browser;
use Buzz\Client\Curl;
use Buzz\Message\Factory\FactoryInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class BrowserTest extends TestCase
{
    /** @var Curl */
    private $client;

    /** @var FactoryInterface */
    private $factory;

    /** @var Browser */
    private $browser;

    protected function setUp()
    {
        $this->client = $this->getMockBuilder('Buzz\Client\Curl')->getMock();
        $this->factory = $this->getMockBuilder('Buzz\Message\Factory\FactoryInterface')->getMock();

        $this->browser = new Browser($this->client, $this->factory);
    }

    /**
     * @dataProvider provideMethods
     */
    public function testBasicMethods($method, $content)
    {
        $request = $this->getMockBuilder('Buzz\Message\RequestInterface')->getMock();
        $response = new Response(200, [], 'foobar');
        $headers = array('X-Foo: bar');

        $this->factory->expects($this->once())
            ->method('createRequest')
            ->with(strtoupper($method))
            ->will($this->returnValue($request));
        $request->expects($this->once())
            ->method('setHost')
            ->with('http://google.com');
        $request->expects($this->once())
            ->method('setResource')
            ->with('/');
        $request->expects($this->once())
            ->method('addHeaders')
            ->with($headers);
        $request->expects($this->once())
            ->method('setContent')
            ->with($content);
        $this->client->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue($response));

        $actual = $this->browser->$method('http://google.com/', $headers, $content);

        $this->assertInstanceOf('Buzz\Message\Response', $actual);
        $this->assertEquals($response->getBody()->__toString(), $actual->getContent());
    }

    public function provideMethods()
    {
        return array(
            array('get',    ''),
            array('head',   ''),
            array('post',   'content'),
            array('put',    'content'),
            array('delete', 'content'),
        );
    }

    /**
     * @group legacy
     */
    public function testSubmit()
    {
        $request = $this->getMockBuilder('Buzz\Message\Form\FormRequestInterface')->getMock();
        $response = $this->getMockBuilder('Buzz\Message\MessageInterface')->getMock();
        $headers = array('X-Foo: bar');

        $this->factory->expects($this->once())
            ->method('createFormRequest')
            ->will($this->returnValue($request));
        $request->expects($this->once())
            ->method('setMethod')
            ->with('PUT');
        $request->expects($this->once())
            ->method('setHost')
            ->with('http://google.com');
        $request->expects($this->once())
            ->method('setResource')
            ->with('/');
        $request->expects($this->once())
            ->method('addHeaders')
            ->with($headers);
        $request->expects($this->once())
            ->method('setFields')
            ->with(array('foo' => 'bar', 'bar' => 'foo'));
        $this->factory->expects($this->once())
            ->method('createResponse')
            ->will($this->returnValue($response));
        $this->client->expects($this->once())
            ->method('send')
            ->with($request, $response);

        $actual = $this->browser->submit('http://google.com', array('foo' => 'bar', 'bar' => 'foo'), 'PUT', $headers);

        $this->assertSame($response, $actual);
    }

    /**
     * @group legacy
     */
    public function testListener()
    {
        $listener = $this->getMockBuilder('Buzz\Listener\ListenerInterface')->getMock();
        $request = $this->getMockBuilder('Buzz\Message\RequestInterface')->getMock();
        $response = $this->getMockBuilder('Buzz\Message\MessageInterface')->getMock();

        $listener->expects($this->once())
            ->method('preSend')
            ->with($request);
        $listener->expects($this->once())
            ->method('postSend')
            ->with($request, $response);

        $this->browser->setListener($listener);
        $this->assertSame($listener, $this->browser->getListener());

        $this->browser->send($request, $response);
    }

    /**
     * @group legacy
     */
    public function testLastMessagesLegacy()
    {
        $request = $this->getMockBuilder('Buzz\Message\RequestInterface')->getMock();
        $response = $this->getMockBuilder('Buzz\Message\MessageInterface')->getMock();

        $this->browser->send($request, $response);

        $this->assertSame($request, $this->browser->getLastRequest());
        $this->assertSame($response, $this->browser->getLastResponse());
    }

    public function testLastMessages()
    {
        $request = new Request('GET', 'http://www.google.se');
        $response = new Response(200, [], 'foobar');

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue($response));

        $this->browser->sendRequest($request);

        $this->assertSame($request, $this->browser->getLastRequest());
        $this->assertSame($response, $this->browser->getLastResponse());
    }

    public function testClientMethods()
    {
        $client = $this->getMockBuilder('Buzz\Client\ClientInterface')->getMock();
        $this->browser->setClient($client);
        $this->assertSame($client, $this->browser->getClient());
    }

    public function testFactoryMethods()
    {
        $factory = $this->getMockBuilder('Buzz\Message\Factory\FactoryInterface')->getMock();
        $this->browser->setMessageFactory($factory);
        $this->assertSame($factory, $this->browser->getMessageFactory());
    }

    public function testAddFirstListener()
    {
        $listener = $this->getMockBuilder('Buzz\Listener\ListenerInterface')->getMock();
        $this->browser->addListener($listener);
        $this->assertEquals($listener, $this->browser->getListener());
    }

    public function testAddSecondListener()
    {
        $listener = $this->getMockBuilder('Buzz\Listener\ListenerInterface')->getMock();

        $this->browser->addListener($listener);
        $this->browser->addListener($listener);

        $listenerChain = $this->browser->getListener();

        $this->assertInstanceOf('Buzz\Listener\ListenerChain', $listenerChain);
        $this->assertEquals(2, count($listenerChain->getListeners()));
    }

    public function testAddThirdListener()
    {
        $listener = $this->getMockBuilder('Buzz\Listener\ListenerInterface')->getMock();

        $this->browser->addListener($listener);
        $this->browser->addListener($listener);
        $this->browser->addListener($listener);

        $listenerChain = $this->browser->getListener();

        $this->assertInstanceOf('Buzz\Listener\ListenerChain', $listenerChain);
        $this->assertEquals(3, count($listenerChain->getListeners()));
    }
}
