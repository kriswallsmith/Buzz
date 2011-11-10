<?php

namespace Buzz\Test\Cookie;

use Buzz\Cookie\Cookie;
use Buzz\Cookie\Jar;
use Buzz\Message;

class JarTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessSetCookieHeadersSetsCookies()
    {
        $request = new Message\Request();
        $request->setHost('http://www.example.com');

        $response = new Message\Response();
        $response->addHeader('Set-Cookie: SESSION2=qwerty');
        $response->addHeader('Set-Cookie: SESSION1=asdf');

        $jar = new Jar();
        $jar->processSetCookieHeaders($request, $response);

        $cookies = $jar->getCookies();

        $this->assertEquals(2, count($cookies));
        foreach ($cookies as $cookie) {
            $this->assertEquals('www.example.com', $cookie->getAttribute(Cookie::ATTR_DOMAIN));
            $this->assertTrue(in_array($cookie->getName(), array('SESSION1', 'SESSION2')));
        }
    }

    public function testAddCookieHeadersAddsCookieHeaders()
    {
        $request = new Message\Request();
        $request->setHost('http://www.example.com');

        $cookie = new Cookie();
        $cookie->setName('SESSION');
        $cookie->setValue('asdf');
        $cookie->setAttribute(Cookie::ATTR_DOMAIN, '.example.com');

        $jar = new Jar();
        $jar->setCookies(array($cookie));
        $jar->addCookieHeaders($request);

        $this->assertEquals('SESSION=asdf', $request->getHeader('Cookie'));
    }

    public function testClearExpiredCookiesRemovesExpiredCookies()
    {
        $cookie = new Cookie();
        $cookie->setName('SESSION');
        $cookie->setValue('asdf');
        $cookie->setAttribute(Cookie::ATTR_EXPIRES, 'Fri, 01-Dec-1999 00:00:00 GMT');

        $jar = new Jar();
        $jar->addCookie($cookie);
        $jar->clearExpiredCookies();

        $this->assertEquals(0, count($jar->getCookies()));

        $cookie = new Cookie();
        $cookie->setName('SESSION');
        $cookie->setValue('asdf');
        $cookie->setAttribute(Cookie::ATTR_MAX_AGE, '-60');

        $jar = new Jar();
        $jar->addCookie($cookie);
        $jar->clearExpiredCookies();

        $this->assertEquals(0, count($jar->getCookies()));
    }
}
