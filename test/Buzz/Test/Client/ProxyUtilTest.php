<?php

namespace Buzz\Test\Client;

use Buzz\Client\ProxyUtil;

class ProxyUtilTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @dataProvider provideProxy
     */
    public function testParseProxy($enabled, $proxyInput, $proxy = null, $username = null, $password = null)
    {
        $parsed = ProxyUtil::parseProxy($proxyInput);
        $this->assertEquals($enabled, $parsed['enabled']);
        $this->assertEquals($proxy, $parsed['proxy']);
        $this->assertEquals($username, $parsed['username']);
        $this->assertEquals($password, $parsed['password']);
    }

    /**
     * @dataProvider provideProxy
     */
    public function testParseSystemUppercaseProxy($enabled, $proxyInput, $proxy = null, $username = null, $password = null)
    {
        $_SERVER['HTTP_PROXY'] = $proxyInput;
        $parsed = ProxyUtil::parseProxy();
        $this->assertEquals($enabled, $parsed['enabled']);
        $this->assertEquals($proxy, $parsed['proxy']);
        $this->assertEquals($username, $parsed['username']);
        $this->assertEquals($password, $parsed['password']);
        $this->assertEquals($proxyInput, $parsed['raw']);
    }

    /**
     * @dataProvider provideProxy
     */
    public function testParseSystemLowercaseProxy($enabled, $proxyInput, $proxy = null, $username = null, $password = null)
    {
        $_SERVER['http_proxy'] = $proxyInput;
        $parsed = ProxyUtil::parseProxy();
        $this->assertEquals($enabled, $parsed['enabled']);
        $this->assertEquals($proxy, $parsed['proxy']);
        $this->assertEquals($username, $parsed['username']);
        $this->assertEquals($password, $parsed['password']);
    }

    public function provideProxy()
    {
        return array(
            array(false, '', ''),

            array(true, 'http://proxy.example.com:3333',  'http://proxy.example.com:3333'),
            array(true, 'https://proxy.example.com:3333', 'https://proxy.example.com:3333'),
            array(true, 'proxy.example.com:3333',         'proxy.example.com:3333'),

            array(true, 'http://someuser:PRIVATE@proxy.example.com:3333',  'http://proxy.example.com:3333',  'someuser', 'PRIVATE'),
            array(true, 'https://someuser:PRIVATE@proxy.example.com:3333', 'https://proxy.example.com:3333', 'someuser', 'PRIVATE'),
            array(true, 'someuser:PRIVATE@proxy.example.com:3333',         'proxy.example.com:3333',         'someuser', 'PRIVATE'),

            array(true, 'http://someuser@proxy.example.com:3333',  'http://proxy.example.com:3333',  'someuser'),
            array(true, 'https://someuser@proxy.example.com:3333', 'https://proxy.example.com:3333', 'someuser'),
            array(true, 'someuser@proxy.example.com:3333',         'proxy.example.com:3333',         'someuser'),

            array(true, 'http://someuser:@proxy.example.com:3333',  'http://proxy.example.com:3333',  'someuser'),
            array(true, 'https://someuser:@proxy.example.com:3333', 'https://proxy.example.com:3333', 'someuser'),
            array(true, 'someuser:@proxy.example.com:3333',         'proxy.example.com:3333',         'someuser'),
        );
    }
}
