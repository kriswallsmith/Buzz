<?php

namespace Buzz\Test\Client;

use Buzz\Client\AbstractStream;
use Buzz\Message;

class StreamClient extends AbstractStream
{
}

class AbstractStreamTest extends \PHPUnit_Framework_TestCase
{
    protected $originalEnv = array();

    public function setUp()
    {
        // Setup testing environment.
        foreach (array('HTTP_PROXY', 'http_proxy') as $key) {
            if (isset($_SERVER[$key])) {
                $this->originalEnv[$key] = $_SERVER[$key];
                unset($_SERVER[$key]);
            } else {
                unset($this->originalEnv[$key]);
            }
        }
    }

    public function tearDown()
    {
        // Restore original enviornment.
        foreach (array('HTTP_PROXY', 'http_proxy') as $key) {
            if (isset($this->originalEnv[$key])) {
                $_SERVER[$key] = $this->originalEnv[$key];
            } else {
                unset($_SERVER[$key]);
            }
        }
    }

    public function testConvertsARequestToAContextArray()
    {
        list($request, $expected) = $this->getTestRequestAndExpectedStreamContextArray();

        $client = new StreamClient();

        // Test defaults.
        $this->assertEquals($expected, $client->getStreamContextArray($request));

        $client->setMaxRedirects(5);
        $expected['http']['max_redirects'] = 5;
        $this->assertEquals($expected, $client->getStreamContextArray($request));

        $client->setIgnoreErrors(false);
        $expected['http']['ignore_errors'] = false;
        $this->assertEquals($expected, $client->getStreamContextArray($request));

        $client->setTimeout(10);
        $expected['http']['timeout'] = 10;
        $this->assertEquals($expected, $client->getStreamContextArray($request));

        $client->setVerifyPeer(true);
        $expected['ssl']['verify_peer'] = true;
        $this->assertEquals($expected, $client->getStreamContextArray($request));
    }

    /**
     * User should be able to enable, define and authenticate against proxy programmatically
     * 
     * @param string $proxyOutput
     * @param string $proxy
     * @param string|null $proxyUsername
     * @param string|null $proxyPassword
     * @dataProvider provideProxy
     */
     public function testProxy($proxyOutput, $proxy, $proxyUsername = null, $proxyPassword = null)
     {
        list($request, $expected) = $this->getTestRequestAndExpectedStreamContextArray();

        $client = new StreamClient();
        $client
            ->setProxyEnabled(true)
            ->setProxy($proxy)
            ->setProxyUsername($proxyUsername)
            ->setProxyPassword($proxyPassword)
        ;

        $expected['http']['proxy']           = $proxyOutput;
        $expected['http']['request_fulluri'] = true;

        $this->assertEquals($expected, $client->getStreamContextArray($request));
    }

    /**
     * Client should default to using system defined proxy
     * 
     * @dataProvider provideSystemProxy
     */
    public function testSystemProxy($proxyInput, $proxyOutput)
    {
        $_SERVER['HTTP_PROXY'] = $proxyInput;

        list($request, $expected) = $this->getTestRequestAndExpectedStreamContextArray();

        $client = new StreamClient();

        $expected['http']['proxy']           = $proxyOutput;
        $expected['http']['request_fulluri'] = true;

        $this->assertEquals($expected, $client->getStreamContextArray($request));
    }

    /**
     * User should be able to override system defined proxy username and password programmatically
     * 
     * @param string $proxyInput
     * @param string $proxyOutput
     * @param string $proxyUsername
     * @param string $proxyPassword
     * @dataProvider provideSystemProxyProgramaticAuthOverride
     */
    public function testSystemProxyProgramaticAuthOverride($proxyInput, $proxyOutput, $proxyUsername, $proxyPassword)
    {
        $_SERVER['HTTP_PROXY'] = $proxyInput;

        list($request, $expected) = $this->getTestRequestAndExpectedStreamContextArray();

        $client = new StreamClient();
        $client
            ->setProxyUsername($proxyUsername)
            ->setProxyPassword($proxyPassword)
        ;

        $expected['http']['proxy'] = $proxyOutput;
        $expected['http']['request_fulluri'] = true;

        $this->assertEquals($expected, $client->getStreamContextArray($request));
    }

    /**
     * User should be able to disable system proxy programmatically
     */
    public function testProgramaticDisableSystemProxy()
    {
        $_SERVER['HTTP_PROXY'] = 'http://proxy.example.com';

        list($request, $expected) = $this->getTestRequestAndExpectedStreamContextArray();

        $client = new StreamClient();
        $client->setProxyEnabled(false);

        $this->assertEquals($expected, $client->getStreamContextArray($request));
    }

    public function provideProxy()
    {
        return array(
            array('tcp://proxy.example.com:3333', 'http://proxy.example.com:3333'),
            array('ssl://proxy.example.com:3333', 'https://proxy.example.com:3333'),
            array('tcp://proxy.example.com:3333', 'proxy.example.com:3333'),

            array('tcp://someuser:@proxy.example.com:3333', 'http://proxy.example.com:3333',  'someuser'),
            array('ssl://someuser:@proxy.example.com:3333', 'https://proxy.example.com:3333', 'someuser'),
            array('tcp://someuser:@proxy.example.com:3333', 'proxy.example.com:3333',         'someuser'),

            array('tcp://someuser:PRIVATE@proxy.example.com:3333', 'http://proxy.example.com:3333',  'someuser', 'PRIVATE'),
            array('ssl://someuser:PRIVATE@proxy.example.com:3333', 'https://proxy.example.com:3333', 'someuser', 'PRIVATE'),
            array('tcp://someuser:PRIVATE@proxy.example.com:3333', 'proxy.example.com:3333',         'someuser', 'PRIVATE'),
        );
    }

    public function provideSystemProxy()
    {
        return array(
            array('http://proxy.example.com:3333',  'tcp://proxy.example.com:3333'),
            array('https://proxy.example.com:3333', 'ssl://proxy.example.com:3333'),
            array('proxy.example.com:3333',         'tcp://proxy.example.com:3333'),

            array('http://someuser:PRIVATE@proxy.example.com:3333',  'tcp://someuser:PRIVATE@proxy.example.com:3333'),
            array('https://someuser:PRIVATE@proxy.example.com:3333', 'ssl://someuser:PRIVATE@proxy.example.com:3333'),
            array('someuser:PRIVATE@proxy.example.com:3333',         'tcp://someuser:PRIVATE@proxy.example.com:3333'),
        );
    }

    public function provideSystemProxyProgramaticAuthOverride()
    {
        return array(
            array('http://proxy.example.com:3333',  'tcp://someuser:PRIVATE@proxy.example.com:3333', 'someuser', 'PRIVATE'),
            array('https://proxy.example.com:3333', 'ssl://someuser:PRIVATE@proxy.example.com:3333', 'someuser', 'PRIVATE'),
            array('proxy.example.com:3333',         'tcp://someuser:PRIVATE@proxy.example.com:3333', 'someuser', 'PRIVATE'),

            array('http://someuser:PRIVATE@proxy.example.com:3333',  'tcp://anotheruser:SECRET@proxy.example.com:3333', 'anotheruser', 'SECRET'),
            array('https://someuser:PRIVATE@proxy.example.com:3333', 'ssl://anotheruser:SECRET@proxy.example.com:3333', 'anotheruser', 'SECRET'),
            array('someuser:PRIVATE@proxy.example.com:3333',         'tcp://anotheruser:SECRET@proxy.example.com:3333', 'anotheruser', 'SECRET'),
        );
    }

    protected function getTestRequestAndExpectedStreamContextArray()
    {
        $request = new Message\Request('POST', '/resource/123', 'http://example.com');
        $request->addHeader('Content-Type: application/x-www-form-urlencoded');
        $request->addHeader('Content-Length: 15');
        $request->setContent('foo=bar&bar=baz');
        return array($request, array(
            'http' => array(
                'method'           => 'POST',
                'header'           => "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: 15",
                'content'          => 'foo=bar&bar=baz',
                'protocol_version' => 1.0,
                'ignore_errors'    => true,
                'max_redirects'    => 5,
                'timeout'          => 5,
            ),
            'ssl' => array(
                'verify_peer'      => true,
            ),
        ));
    }
}
