<?php

namespace Buzz\Service\RightScale;

use Buzz\Message;

require_once __DIR__.'/../../../../lib/Buzz/ClassLoader.php';
\Buzz\ClassLoader::register();

class BrowserTest extends \PHPUnit_Framework_TestCase
{
  protected $browser;

  public function setUp()
  {
    $keys = array('RIGHTSCALE_ACCOUNT_ID', 'RIGHTSCALE_USERNAME', 'RIGHTSCALE_PASSWORD');
    if (array_diff($keys, array_keys($_SERVER)))
    {
      throw new Exception(implode("\n", array_merge(
        array('Please set the following environment variables:', ''),
        array_map(function($key) { return ' - '.$key; }, $keys)
      )));
    }

    $this->browser = new Browser(
      $_SERVER['RIGHTSCALE_ACCOUNT_ID'],
      $_SERVER['RIGHTSCALE_USERNAME'],
      $_SERVER['RIGHTSCALE_PASSWORD']
    );
  }

  public function testPrepareRequestSetsHeaders()
  {
    $request = new Message\Request();
    $this->browser->prepareRequest($request);

    $this->assertEquals($request->getHeader('X-API-VERSION'), '1.0');
    $this->assertTrue(0 < strlen($request->getHeader('Authorization')));
  }
}
