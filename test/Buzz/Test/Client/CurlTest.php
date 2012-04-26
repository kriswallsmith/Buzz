<?php

namespace Buzz\Test\Client;

use Buzz\Client\Curl;

class CurlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideResponses
     */
    public function testGetLastResponse($headers, $content, $eol, $expected)
    {
        $curl = new Curl();

        $r = new \ReflectionObject($curl);
        $m = $r->getMethod('getLastResponse');
        $m->setAccessible(true);

        $this->assertEquals($expected, $m->invoke($curl, $headers.str_repeat($eol, 2).$content, strlen($headers.str_repeat($eol, 2))));
    }

    public function provideResponses()
    {
        $redirectedHeader = "HTTP/1.0 302 Moved Temporarily
Location: http://feeds.feedburner.com/KrisWallsmith

Blah blah.

HTTP/1.0 200 OK
Content-Type: text/xml; charset=UTF-8";

        $redirectedBody = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<foo>

  <bar />

</foo>";

        $normalHeader = "HTTP/1.0 200 OK
Content-Type: text/xml; charset=UTF-8";

        $normalBody = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<foo>

  <bar />

</foo>";

        $eol = "
";

        $fakeBody = "HTTP/1.0 500 Fake error
Content-Type: text/xml; charset=UTF-8

Fake content";

        return array(
            array($normalHeader, $normalBody, $eol, array(explode($eol, $normalHeader), $normalBody)),
            array($redirectedHeader, $redirectedBody, $eol, array(explode($eol, $normalHeader), $normalBody)),
            array(str_replace($eol, "\n", $redirectedHeader), $redirectedBody, "\n", array(explode($eol, $normalHeader), $normalBody)),
            array(str_replace($eol, "\r\n", $redirectedHeader), $redirectedBody, "\r\n", array(explode($eol, $normalHeader), $normalBody)),
            array($normalHeader, $fakeBody, $eol, array(explode($eol, $normalHeader), $fakeBody)),
        );
    }
}
