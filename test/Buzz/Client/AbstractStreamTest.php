<?php

namespace Buzz\Client;

use Buzz\Message;

require_once __DIR__.'/../../../lib/Buzz/ClassLoader.php';
\Buzz\ClassLoader::register();

class StreamClient extends AbstractStream
{
}

class AbstractStreamTest extends \PHPUnit_Framework_TestCase
{
  public function testConvertsARequestToAContextArray()
  {
    $request = new Message\Request('POST', '/resource/123', 'http://example.com');
    $request->addHeader('Content-Type: application/x-www-form-urlencoded');
    $request->addHeader('Content-Length: 15');
    $request->setContent('foo=bar&bar=baz');

    $client = new StreamClient();
    $client->setMaxRedirects(5);
    $expected = array('http' => array(
      'method'           => 'POST',
      'header'           => "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: 15",
      'content'          => 'foo=bar&bar=baz',
      'protocol_version' => 1.0,
      'ignore_errors'    => true,
      'max_redirects'    => 5,
      'timeout'          => 5,
    ));

    $this->assertEquals($client->getStreamContextArray($request), $expected);
  }
}
