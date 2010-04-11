<?php

namespace Buzz\Cookie;

require_once __DIR__.'/../../../lib/Buzz/ClassLoader.php';
\Buzz\ClassLoader::register();

require_once 'PHPUnit/Framework/TestCase.php';

class CookieTest extends \PHPUnit_Framework_TestCase
{
  public function testFromSetCookieHeaderSetsCookieAttributes()
  {
    $cookie = new Cookie();
    $cookie->fromSetCookieHeader('SESSION=asdf; expires='.date('r', strtotime('2000-01-01 00:00:00')).'; path=/; domain=.example.com', 'www.example.com');

    $this->assertEquals($cookie->getName(), 'SESSION');
    $this->assertEquals($cookie->getValue(), 'asdf');
    $this->assertEquals($cookie->getAttributes(), array(
      'expires' => date('r', strtotime('2000-01-01 00:00:00')),
      'path'    => '/',
      'domain'  => '.example.com',
    ));
  }

  public function testToCookieHeaderFormatsACookieHeader()
  {
    $cookie = new Cookie();
    $cookie->setName('SESSION');
    $cookie->setValue('asdf');

    $this->assertEquals($cookie->toCookieHeader(), 'Cookie: SESSION=asdf');
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
}
