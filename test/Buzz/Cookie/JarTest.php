<?php

namespace Buzz\Cookie;

use Buzz\Message;

require_once __DIR__.'/../../../lib/Buzz/ClassLoader.php';
\Buzz\ClassLoader::register();

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

        $this->assertEquals(count($cookies), 2);
        foreach ($cookies as $cookie) {
            $this->assertEquals($cookie->getAttribute(Cookie::ATTR_DOMAIN), 'www.example.com');
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

        $this->assertEquals($request->getHeader('Cookie'), 'SESSION=asdf');
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

        $this->assertEquals(count($jar->getCookies()), 0);

        $cookie = new Cookie();
        $cookie->setName('SESSION');
        $cookie->setValue('asdf');
        $cookie->setAttribute(Cookie::ATTR_MAX_AGE, '-60');

        $jar = new Jar();
        $jar->addCookie($cookie);
        $jar->clearExpiredCookies();

        $this->assertEquals(count($jar->getCookies()), 0);
    }
}
