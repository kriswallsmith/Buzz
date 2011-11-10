<?php

namespace Buzz\Test\Client;

use Buzz\Client\Curl;

class CurlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideResponses
     */
    public function testGetLastResponse($raw, $expected)
    {
        $curl = new Curl();

        $r = new \ReflectionObject($curl);
        $m = $r->getMethod('getLastResponse');
        $m->setAccessible(true);

        $this->assertEquals($expected, $m->invoke($curl, $raw));
    }

    public function provideResponses()
    {
        $redirected = <<<EOD
HTTP/1.0 302 Moved Temporarily
Location: http://feeds.feedburner.com/KrisWallsmith

Blah blah.

HTTP/1.0 200 OK
Content-Type: text/xml; charset=UTF-8

<?xml version="1.0" encoding="UTF-8"?>
<foo>

  <bar />

</foo>

EOD;

        $normal = <<<EOD
HTTP/1.0 200 OK
Content-Type: text/xml; charset=UTF-8

<?xml version="1.0" encoding="UTF-8"?>
<foo>

  <bar />

</foo>

EOD;

        $eol = <<<EOD

EOD;

        return array(
            array($normal, $normal),
            array($redirected, $normal),
            array(str_replace($eol, "\n", $redirected), str_replace($eol, "\n", $normal)),
            array(str_replace($eol, "\r\n", $redirected), str_replace($eol, "\r\n", $normal)),
        );
    }
}
