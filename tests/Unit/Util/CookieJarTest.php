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
        // Create a CookieJar
        $jar = new CookieJar();

        // Prepare two cookies
        $cookie1 = new Cookie();
        $cookie1->setName('SESSION');
        $cookie1->setValue('asdf');
        $cookie1->setAttribute(Cookie::ATTR_DOMAIN, '.example.com');

        $cookie2 = new Cookie();
        $cookie2->setName('foo');
        $cookie2->setValue('bar');
        $cookie2->setAttribute(Cookie::ATTR_DOMAIN, '.example.com');

        // Add one cookie to the CookieJar
        $jar->setCookies([$cookie1]);
        $request = new Request('GET', 'http://www.example.com');
        $newRequest = $jar->addCookieHeaders($request);
        $this->assertEquals('SESSION=asdf', $newRequest->getHeaderLine('Cookie'));

        // Add the second cookie
        $jar->addCookie($cookie2);
        $newRequest = $jar->addCookieHeaders($request);
        $this->assertEquals('SESSION=asdf, foo=bar', $newRequest->getHeaderLine('Cookie'));
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
