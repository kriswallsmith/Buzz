<?php
namespace Buzz\Example;

use Buzz\Client\MultiCurl;
use Buzz\Message\Request;
use Buzz\Message\Response;

function psr0_autoload($className) {
  $className = ltrim($className, '\\');
  $fileName  = '';
  $namespace = '';
  if ($lastNsPos = strrpos($className, '\\')) {
    $namespace = substr($className, 0, $lastNsPos);
    $className = substr($className, $lastNsPos + 1);
    $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
  }
  $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

  require $fileName;
}

error_reporting(E_ALL);
spl_autoload_register("\\Buzz\\Example\\psr0_autoload");
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__).'/lib/');


function send_request($client, $resource, $host, $callback) {
  $request = new Request('GET', $resource, $host);
  $request->setProtocolVersion(1.1);
  $request->addHeader('User-Agent: php-'.phpversion());
  $request->addHeader('Accept: */*');
  $response = new Response();
  $options = array(
    'callback' => $callback,
    'errback' => function($request, $transport_error, $curl_error_code) {
      throw new Exception("Curl Transport Error: " . $transport_error);
    }
  );
  $client->send($request, $response, $options);
}

function handle_response($request, $response, $options) {
  echo "*** GET {$request->getUrl()}\n";
  echo "HTTP ", $response->getStatusCode(), " ", $response->getReasonPhrase(), "\n";
  echo "  ", implode("\n  ", $response->getHeaders()), "\n";
  echo "(", strlen($response->getContent()), " bytes)\n";
}

$client = new MultiCurl();
$client->setTimeout(10);
send_request($client, "/", "http://lxr.php.net/", "\\Buzz\\Example\\handle_response");
send_request($client, "/", "http://www.php.net/", "\\Buzz\\Example\\handle_response");
send_request($client, "/", "http://www.google.com/", "\\Buzz\\Example\\handle_response");
send_request($client, "/", "http://www.yahoo.com/", "\\Buzz\\Example\\handle_response");
while (!$client->isDone()) {
  $client->proceed();
}

echo "Done\n";