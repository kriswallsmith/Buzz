<?php

namespace Buzz\Test\Cookie;

use Buzz\Util\Cookie;
use Buzz\Message;

class CookieTest extends \PHPUnit_Framework_TestCase
{
    public function testFromSetCookieHeaderSetsCookieAttributes()
    {
        $cookie = new Cookie();
        $cookie->fromSetCookieHeader('SESSION=asdf; expires='.date('r', strtotime('2000-01-01 00:00:00')).'; path=/; domain=.example.com; secure', 'www.example.com');

        $this->assertEquals('SESSION', $cookie->getName());
        $this->assertEquals('asdf', $cookie->getValue());
        $this->assertEquals(array(
            'expires' => date('r', strtotime('2000-01-01 00:00:00')),
            'path'    => '/',
            'domain'  => '.example.com',
            'secure'  => null,
        ), $cookie->getAttributes());
    }

    public function testFromSetCookieHeaderFallsBackToIssuingDomain()
    {
        $cookie = new Cookie();
        $cookie->fromSetCookieHeader('SESSION=asdf', 'example.com');

        $this->assertEquals('example.com', $cookie->getAttribute(Cookie::ATTR_DOMAIN));
    }

    public function testToCookieHeaderFormatsACookieHeader()
    {
        $cookie = new Cookie();
        $cookie->setName('SESSION');
        $cookie->setValue('asdf');

        $this->assertEquals('Cookie: SESSION=asdf', $cookie->toCookieHeader());
    }

    public function testMatchesDomainMatchesSimpleDomains()
    {
        $cookie = new Cookie();
        $cookie->setAttribute('domain', 'nytimes.com');

        $this->assertTrue($cookie->matchesDomain('nytimes.com'));
        $this->assertFalse($cookie->matchesDomain('google.com'));
    }

    public function testMatchesDomainMatchesSubdomains()
    {
        $cookie = new Cookie();
        $cookie->setAttribute('domain', '.nytimes.com');

        $this->assertTrue($cookie->matchesDomain('nytimes.com'));
        $this->assertTrue($cookie->matchesDomain('blogs.nytimes.com'));
        $this->assertFalse($cookie->matchesDomain('google.com'));
    }

    public function testIsExpiredChecksMaxAge()
    {
        $cookie = new Cookie();
        $cookie->setAttribute('max-age', 60);

        $this->assertFalse($cookie->isExpired());

        $cookie = new Cookie();
        $cookie->setCreatedAt(strtotime('-1 hour'));
        $cookie->setAttribute('max-age', 60);

        $this->assertTrue($cookie->isExpired());
    }

    public function testIsExpiredChecksExpires()
    {
        $cookie = new Cookie();
        $cookie->setAttribute('expires', date('r', strtotime('+1 week')));

        $this->assertFalse($cookie->isExpired());

        $cookie = new Cookie();
        $cookie->setAttribute('expires', date('r', strtotime('-1 month')));

        $this->assertTrue($cookie->isExpired());
    }

    public function testMatchesPathChecksPath()
    {
        $cookie = new Cookie();
        $cookie->setAttribute('path', '/resource');

        $this->assertTrue($cookie->matchesPath('/resource/123'));
        $this->assertFalse($cookie->matchesPath('/login'));

        $cookie = new Cookie();
        $this->assertTrue($cookie->matchesPath('/resource/123'));
    }

    public function testMatchesRequestChecksDomain()
    {
        $request = new Message\Request();
        $request->setHost('http://example.com');

        $cookie = new Cookie();
        $cookie->setAttribute(Cookie::ATTR_DOMAIN, 'example.com');

        $this->assertTrue($cookie->matchesRequest($request));

        $cookie = new Cookie();
        $cookie->setAttribute(Cookie::ATTR_DOMAIN, 'foo.com');

        $this->assertFalse($cookie->matchesRequest($request));
    }

    public function testMatchesRequestChecksPath()
    {
        $request = new Message\Request();
        $request->setHost('http://example.com');
        $request->setResource('/foo/bar');

        $cookie = new Cookie();
        $cookie->setAttribute(Cookie::ATTR_DOMAIN, 'example.com');
        $cookie->setAttribute(Cookie::ATTR_PATH, '/foo');

        $this->assertTrue($cookie->matchesRequest($request));

        $cookie = new Cookie();
        $cookie->setAttribute(Cookie::ATTR_DOMAIN, 'example.com');
        $cookie->setAttribute(Cookie::ATTR_PATH, '/foo/bar/baz');

        $this->assertFalse($cookie->matchesRequest($request));
    }

    public function testMatchesRequestChecksSecureAttribute()
    {
        $request = new Message\Request();
        $request->setHost('https://example.com');

        $cookie = new Cookie();
        $cookie->setAttribute(Cookie::ATTR_DOMAIN, 'example.com');
        $cookie->setAttribute(Cookie::ATTR_SECURE, null);

        $this->assertTrue($cookie->matchesRequest($request));

        $request = new Message\Request();
        $request->setHost('http://example.com');

        $this->assertFalse($cookie->matchesRequest($request));
    }
}
