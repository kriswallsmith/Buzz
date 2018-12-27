<?php

declare(strict_types=1);

namespace Buzz\Test\Unit\Cookie;

use Buzz\Util\Cookie;
use Buzz\Util\CookieJar;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;

class CookieJarTest extends TestCase
{
    public function testProcessSetCookieHeadersSetsCookies()
    {
        $request = new Request('GET', 'http://www.example.com');

        $response = new Response(200, [
            'Set-Cookie' => ['SESSION2=qwerty', 'SESSION1=asdf'],
        ]);

        $jar = new CookieJar();
        $jar->processSetCookieHeaders($request, $response);

        $cookies = $jar->getCookies();

        $this->assertEquals(2, \count($cookies));
        foreach ($cookies as $cookie) {
            $this->assertEquals('www.example.com', $cookie->getAttribute(Cookie::ATTR_DOMAIN));
            $this->assertTrue(\in_array($cookie->getName(), ['SESSION1', 'SESSION2']));
        }
    }

    public function testAddCookieHeadersAddsCookieHeaders()
    {
        $cookie = new Cookie();
        $cookie->setName('SESSION');
        $cookie->setValue('asdf');
        $cookie->setAttribute(Cookie::ATTR_DOMAIN, '.example.com');

        $jar = new CookieJar();
        $jar->setCookies([$cookie]);

        $request = new Request('GET', 'http://www.example.com');
        $request = $jar->addCookieHeaders($request);
        $this->assertEquals('SESSION=asdf', $request->getHeaderLine('Cookie'));

        // Test with more cookies
        $cookie = new Cookie();
        $cookie->setName('foo');
        $cookie->setValue('bar');
        $cookie->setAttribute(Cookie::ATTR_DOMAIN, '.example.com');

        $jar->addCookie($cookie);
        $request = new Request('GET', 'http://www.example.com');
        $request = $jar->addCookieHeaders($request);
        $this->assertEquals('SESSION=asdf, foo=bar', $request->getHeaderLine('Cookie'));
    }

    public function testClearExpiredCookiesRemovesExpiredCookies()
    {
        $cookie = new Cookie();
        $cookie->setName('SESSION');
        $cookie->setValue('asdf');
        $cookie->setAttribute(Cookie::ATTR_EXPIRES, 'Fri, 01-Dec-1999 00:00:00 GMT');

        $jar = new CookieJar();
        $jar->addCookie($cookie);
        $jar->clearExpiredCookies();

        $this->assertEquals(0, \count($jar->getCookies()));

        $cookie = new Cookie();
        $cookie->setName('SESSION');
        $cookie->setValue('asdf');
        $cookie->setAttribute(Cookie::ATTR_MAX_AGE, '-60');

        $jar = new CookieJar();
        $jar->addCookie($cookie);
        $jar->clearExpiredCookies();

        $this->assertEquals(0, \count($jar->getCookies()));
    }

    public function testClear()
    {
        $cookie = new Cookie();
        $cookie->setName('SESSION');
        $cookie->setValue('asdf');
        $cookie->setAttribute(Cookie::ATTR_EXPIRES, 'Fri, 01-Dec-1999 00:00:00 GMT');

        $jar = new CookieJar();
        $jar->addCookie($cookie);
        $this->assertEquals(1, \count($jar->getCookies()));

        $jar->clear();
        $this->assertEquals(0, \count($jar->getCookies()));
    }
}
