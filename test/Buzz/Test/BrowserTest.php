<?php

namespace Buzz\Test;

use Buzz\Browser;
use Buzz\Client\Curl;

use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class BrowserTest extends TestCase
{
    /** @var Curl */
    private $client;

    /** @var Browser */
    private $browser;

    protected function setUp()
    {
        $this->client = $this->getMockBuilder('Buzz\Client\Curl')->getMock();

        $this->browser = new Browser($this->client);
    }

    /**
     * @dataProvider provideMethods
     */
    public function testBasicMethods($method, $content)
    {
        $response = new Response(200, [], 'foobar');
        $headers = ['X-Foo'=>'bar'];

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue($response));

        $actual = $this->browser->$method('http://google.com/', $headers, $content);

        $this->assertInstanceOf(ResponseInterface::class, $actual);
        $this->assertEquals($response->getBody()->__toString(), $actual->getBody()->__toString());
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
}
