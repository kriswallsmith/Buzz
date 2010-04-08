<?php

namespace Buzz\Service\RightScale;

use Buzz\Message;

$keys = array('RIGHTSCALE_ACCOUNT_ID', 'RIGHTSCALE_USERNAME', 'RIGHTSCALE_PASSWORD');
if (array_diff($keys, array_keys($_SERVER)))
{
  echo "\nPlease set the following environment variables:\n\n";
  foreach ($keys as $key)
  {
    echo " - $key\n";
  }
  echo "\n";

  exit(1);
}

include __DIR__.'/../../../../bootstrap/unit.php';

$t = new \LimeTest(2);

$rightscale = new Browser(
  $_SERVER['RIGHTSCALE_ACCOUNT_ID'],
  $_SERVER['RIGHTSCALE_USERNAME'],
  $_SERVER['RIGHTSCALE_PASSWORD']
);

// ->preSend()
$t->diag('->preSend()');

$request = new Message\Request();
$rightscale->preSend($request, new Message\Response());
$t->is($request->getHeader('X-API-VERSION'), '1.0', '->preSend() sets the API version header');
$t->ok($request->getHeader('Authorization'), '->preSend() sets an authentication header');
