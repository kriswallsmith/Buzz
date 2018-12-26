<?php

declare(strict_types=1);

namespace Buzz\Test\Integration;

use Buzz\Browser;
use Buzz\Client\BuzzClientInterface;
use Buzz\Client\Curl;
use Buzz\Client\FileGetContents;
use Buzz\Client\MultiCurl;
use Buzz\Exception\InvalidArgumentException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ConfigurationTest extends TestCase
{
    public function testBrowserPassingOption()
    {
        $request = new Request('GET', '/');
        $options = ['foobar' => true, 'timeout' => 4];

        $client = $this->getMockBuilder(BuzzClientInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['sendRequest'])
            ->getMock();

        $client->expects($this->once())
            ->method('sendRequest')
            ->with($this->anything(), $this->equalTo($options))
            ->willReturn(new Response());

        $browser = new Browser($client, new Psr17Factory());
        $browser->sendRequest($request, $options);
    }

    /**
     * @dataProvider clientClassProvider
     */
    public function testOptionInConstructor($class)
    {
        $client = new $class(new Psr17Factory(), ['timeout' => 4]);
        $this->assertInstanceOf($class, $client);
    }

    /**
     * @dataProvider clientClassProvider
     */
    public function testOptionInSendRequest($class)
    {
        if (!isset($_SERVER['BUZZ_TEST_SERVER']) || empty($_SERVER['BUZZ_TEST_SERVER'])) {
            $this->markTestSkipped('The test server is not configured.');
        }

        $client = new $class(new Psr17Factory(), []);

        $response = $client->sendRequest(new Request('GET', $_SERVER['BUZZ_TEST_SERVER']), ['timeout' => 4]);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @dataProvider clientClassProvider
     */
    public function testWrongOptionInConstructor($class)
    {
        $this->expectException(InvalidArgumentException::class);
        new $class(new Psr17Factory(), ['foobar' => true]);
    }

    /**
     * @dataProvider clientClassProvider
     */
    public function testWrongOptionInSendRequest($class)
    {
        $this->expectException(InvalidArgumentException::class);
        $client = new $class(new Psr17Factory(), []);

        $client->sendRequest(new Request('GET', '/'), ['foobar' => true]);
    }

    public function clientClassProvider()
    {
        yield [FileGetContents::class];
        yield [MultiCurl::class];
        yield [Curl::class];
    }
}
