<?php

namespace Buzz\Message;

include __DIR__.'/../../../bootstrap/unit.php';

$t = new \LimeTest(16);

// ->__construct()
$t->diag('->__construct()');

$request = new Request('HEAD', '/resource/123', 'http://example.com');
$t->is($request->getMethod(), 'HEAD', '->__construct() sets the method');
$t->is($request->getResource(), '/resource/123', '->__construct() sets the resource');
$t->is($request->getHost(), 'http://example.com', '->__construct() sets the host');

// ->getUrl()
$t->diag('->getUrl()');

$request = new Request();
$request->setHost('http://example.com');
$request->setResource('/resource/123');
$t->is($request->getUrl(), 'http://example.com/resource/123', '->getUrl() combines host and resource');

// ->fromUrl()
$t->diag('->fromUrl()');

$request = new Request();
$request->fromUrl('http://example.com/resource/123?foo=bar');
$t->is($request->getHost(), 'http://example.com', '->fromUrl() sets the host value');
$t->is($request->getResource(), '/resource/123?foo=bar', '->fromUrl() sets the resource value');

$request = new Request();
$request->fromUrl('http://example.com');
$t->is($request->getResource(), '/', '->fromUrl() defaults the resource to "/"');

$request = new Request();
$request->fromUrl('http://example.com?foo=bar');
$t->is($request->getResource(), '/?foo=bar', '->fromUrl() adds a slash when necessary');

$request = new Request();
$request->fromUrl('/foo#foo');
$t->is($request->getHost(), null, '->fromUrl() does not set a host if none is provided');
$t->is($request->getResource(), '/foo', '->fromUrl() ignores URL fragments');

$request = new Request();
$request->fromUrl('example.com');
$t->is($request->getHost(), 'http://example.com', '->fromUrl() adds a default scheme');

$request = new Request();
$request->fromUrl('example.com/foo');
$t->is($request->getHost(), 'http://example.com', '->fromUrl() adds a default scheme');
$t->is($request->getResource(), '/foo', '->fromUrl() adds a default scheme');

// ->isSecure()
$t->diag('->isSecure()');

$request = new Request('GET', '/resource/123', 'http://example.com');
$t->is($request->isSecure(), false, '->isSecure() returns false if the request is not secure');

$request = new Request('GET', '/resource/123', 'https://example.com');
$t->is($request->isSecure(), true, '->isSecure() returns true if the request is secure');

// ->__toString()
$t->diag('->__toString()');

$request = new Request('POST', '/resource/123', 'http://example.com');
$request->setProtocolVersion(1.1);
$request->addHeader('Content-Type: application/x-www-form-urlencoded');
$request->setContent('foo=bar&bar=baz');
$expected = <<<EOF
POST /resource/123 HTTP/1.1
Host: http://example.com
Content-Type: application/x-www-form-urlencoded

foo=bar&bar=baz

EOF;
$t->is((string) $request, $expected, '->__toString() converts the request object to a string');
