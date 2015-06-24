<?php

namespace Buzz\Test\Listener;

use Buzz\Listener\DigestAuthListener;
use Buzz\Message;

class DigestAuthListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testDigestAuthHeader()
    {
        $request = new Message\Request();
        $request->setMethod('GET');
        $request->setResource('/auth-digest');
        $request->setHost('http://test.webdav.org');
        $request->setProtocolVersion('1.1');

        $response = new Message\Response();
        $response->setHeaders(array(
        	"Date: Wed, 24 Jun 2015 21:49:39 GMT",
			"Server: Apache/2.0.54 (Debian GNU/Linux) DAV/2 SVN/1.3.2",
			"WWW-Authenticate: Digest realm=\"test\", nonce=\"5PvRe0oZBQA=874ad6aea3519069f30dfc704e594dde6e01b2a6\", algorithm=MD5, domain=\"/auth-digest/\", qop=\"auth\"",
			"Content-Length: 401",
			"Content-Type: text/html; charset=iso-8859-1"
       	));
        $response->setContent("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">
<html><head>
<title>401 Authorization Required</title>
</head><body>
<h1>Authorization Required</h1>
<p>This server could not verify that you
are authorized to access the document
requested.  Either you supplied the wrong
credentials (e.g., bad password), or your
browser doesn\'t understand how to supply
the credentials required.</p>
</body></html>");

// Simulate the First Request/Response, where the server returns 401
        $listener = new DigestAuthListener('user1', 'user1');
        $listener->preSend($request);
        $listener->postSend($request, $response);

// Simulate sending the second Request using the calculated Authorization Header
        $request = new Message\Request();
        $request->setMethod('GET');
        $request->setResource('/auth-digest');
        $request->setHost('http://test.webdav.org');
        $request->setProtocolVersion('1.1');

        $this->assertEmpty($request->getHeader('Authorization'));

        $listener->preSend($request);

        $this->assertEquals(
        	'Digest username="user1", realm="test", nonce="5PvRe0oZBQA=874ad6aea3519069f30dfc704e594dde6e01b2a6", response="b2cf05a5d3f51d84a8866309aed6cb5d", uri="/auth-digest"',
        	$request->getHeader('Authorization')
        );
    }
}
