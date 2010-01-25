<?php

namespace Buzz;

include __DIR__.'/../../bootstrap/unit.php';

$t = new \LimeTest(4);

// ->fromSetCookieHeader()
$t->diag('->fromSetCookieHeader()');

$cookie = new Cookie();
$cookie->fromSetCookieHeader('SESSION=asdf; expires='.date('r', strtotime('2000-01-01 00:00:00')).'; path=/; domain=.example.com');
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
