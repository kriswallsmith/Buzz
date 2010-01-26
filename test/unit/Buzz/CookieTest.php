<?php

namespace Buzz;

include __DIR__.'/../../bootstrap/unit.php';

$t = new \LimeTest(16);

// ->fromSetCookieHeader()
$t->diag('->fromSetCookieHeader()');

$cookie = new Cookie();
$cookie->fromSetCookieHeader('SESSION=asdf; expires='.date('r', strtotime('2000-01-01 00:00:00')).'; path=/; domain=.example.com', 'www.example.com');
$t->is($cookie->getName(), 'SESSION', '->fromSetCookieHeader() sets the cookie name');
$t->is($cookie->getValue(), 'asdf', '->fromSetCookieHeader() sets the cookie value');
$t->is($cookie->getAttributes(), array(
  'expires' => date('r', strtotime('2000-01-01 00:00:00')),
  'path'    => '/',
  'domain'  => '.example.com',
), '->fromSetCookieHeader() sets the cookie attributes');

// ->toCookieHeader()
$t->diag('->toCookieHeader()');

$cookie = new Cookie();
$cookie->setName('SESSION');
$cookie->setValue('asdf');
$t->is($cookie->toCookieHeader(), 'Cookie: SESSION=asdf', '->toCookieHeader() formats the cookie as a Cookie header');

// ->matchesDomain()
$t->diag('->matchesDomain()');

$cookie = new Cookie();
$cookie->setAttribute('domain', 'nytimes.com');
$t->is($cookie->matchesDomain('nytimes.com'), true, '->matchesDomain() returns true if domains match');
$t->is($cookie->matchesDomain('google.com'), false, '->matchesDomain() returns false if domains do not match');

$cookie = new Cookie();
$cookie->setAttribute('domain', '.nytimes.com');
$t->is($cookie->matchesDomain('nytimes.com'), true, '->matchesDomain() returns true when a dot-domain matches');
$t->is($cookie->matchesDomain('blogs.nytimes.com'), true, '->matchesDomain() returns true with dot-domains against subdomains');
$t->is($cookie->matchesDomain('google.com'), false, '->matchesDomain() returns false when a dot-domain does not match');

// ->isExpired()
$t->diag('->isExpired()');

$cookie = new Cookie();
$cookie->setAttribute('max-age', 60);
$t->is($cookie->isExpired(), false, '->isExpired() returns false if max-age has not expired');

$cookie = new Cookie();
$cookie->setCreatedAt(strtotime('-1 hour'));
$cookie->setAttribute('max-age', 60);
$t->is($cookie->isExpired(), true, '->isExpired() returns true if max-age has expired');

$cookie = new Cookie();
$cookie->setAttribute('expires', date('r', strtotime('+1 week')));
$t->is($cookie->isExpired(), false, '->isExpired() returns false if expires is in the future');

$cookie = new Cookie();
$cookie->setAttribute('expires', date('r', strtotime('-1 month')));
$t->is($cookie->isExpired(), true, '->isExpired() returns true if expires is in the past');

// ->matchesPath()
$t->diag('->matchesPath()');

$cookie = new Cookie();
$t->is($cookie->matchesPath('/resource/123'), true, '->matchesPath() returns true if there is no path attribute');

$cookie = new Cookie();
$cookie->setAttribute('path', '/resource');
$t->is($cookie->matchesPath('/resource/123'), true, '->matchesPath() returns true if the paths match');
$t->is($cookie->matchesPath('/login'), false, '->matchesPath() returns false if the paths do not match');
