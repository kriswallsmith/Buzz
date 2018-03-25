<?php

namespace Buzz\Test\Unit\Client;

use Buzz\Browser;
use Buzz\Client\BuzzClientInterface;
use Buzz\Client\Curl;
use Buzz\Client\FileGetContents;
use Buzz\Client\MultiCurl;
use Buzz\Exception\ClientException;
use Buzz\Message\FormRequestBuilder;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class FunctionalTest extends TestCase
{
    protected function setUp()
    {
        if (!isset($_SERVER['BUZZ_TEST_SERVER'])) {
            $this->markTestSkipped('The test server is not configured.');
        }
    }

    /**
     * @dataProvider provideClientAndMethod
     */
    public function testRequestMethods($client, $method, $async)
    {
        $request = new Request($method, $_SERVER['BUZZ_TEST_SERVER'], [], 'test');
        $response = $this->send($client, $request, $async);

        $data = json_decode($response->getBody()->__toString(), true);

        $this->assertEquals($method, $data['SERVER']['REQUEST_METHOD']);
    }

    /**
     * @dataProvider provideClient
     */
    public function testGetContentType($client, $async)
    {
        $request = new Request('GET', $_SERVER['BUZZ_TEST_SERVER']);
        $response = $this->send($client, $request, $async);

        $data = json_decode($response->getBody()->__toString(), true);
        $this->assertArrayHasKey('SERVER', $data, $response->getBody()->__toString());

        $this->assertArrayNotHasKey('CONTENT_TYPE', $data['SERVER']);
    }

    /**
     * @dataProvider provideClient
     */
    public function testFormPost($client, $async)
    {
        $request = new Request(
            'POST',
            $_SERVER['BUZZ_TEST_SERVER'],
            ['Content-Type'=>'application/x-www-form-urlencoded'],
            http_build_query(['company[name]'=>'Google'])
        );
        $response = $this->send($client, $request, $async);

        $data = json_decode($response->getBody()->__toString(), true);

        $this->assertStringStartsWith('application/x-www-form-urlencoded', $data['SERVER']['CONTENT_TYPE']);
        $this->assertEquals('Google', $data['POST']['company']['name']);
    }

    /**
     * @dataProvider provideClient
     */
    public function testFormPostWithRequestBuilder($client, $async)
    {
        if ($async) {
            $this->markTestSkipped('Skipping for async requests');
        }

        $builder = new FormRequestBuilder();
        $builder->addField('company[name]', 'Google');
        $browser = new Browser($client);
        $response = $browser->submitForm($_SERVER['BUZZ_TEST_SERVER'], $builder->build());

        $data = json_decode($response->getBody()->__toString(), true);

        $this->assertStringStartsWith('application/x-www-form-urlencoded', $data['SERVER']['CONTENT_TYPE']);
        $this->assertEquals('Google', $data['POST']['company']['name']);
    }

    public function testMultiCurlExecutesRequestsConcurently()
    {
        $client = new MultiCurl(['timeout'=>30]);

        $calls = array();
        $callback = function(RequestInterface $request, ResponseInterface $response = null, ClientException $exception = null) use(&$calls) {
            $calls[] = func_get_args();
        };

        for ($i = 3; $i > 0; $i--) {
            $request = new Request('GET', $_SERVER['BUZZ_TEST_SERVER'].'?delay='.$i);
            $client->sendAsyncRequest($request, array('callback' => $callback));
        }

        $client->flush();
        $this->assertCount(3, $calls);

        foreach ($calls as $i => $call) {
            /** @var ResponseInterface $response */
            $response = $call[1];
            $body = $response->getBody()->__toString();
            $array = json_decode($body, true);
            // Make sure the order is correct
            $this->assertEquals($i+1, $array['GET']['delay']);
        }
    }

    public function provideClient()
    {
        return array(
            array(new Curl(), false),
            array(new FileGetContents(), false),
            array(new MultiCurl(), false),
            array(new MultiCurl(), true),
        );
    }

    public function provideClientAndMethod()
    {
        // HEAD is intentionally omitted
        // http://stackoverflow.com/questions/2603104/does-mod-php-honor-head-requests-properly

        $methods = array('GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS');
        $clients = $this->provideClient();

        foreach ($clients as $client) {
            foreach ($methods as $method) {
                yield array($client[0], $method, $client[1]);
            }
        }
    }

    private function send(BuzzClientInterface $client, RequestInterface $request, bool $async):  ResponseInterface
    {
        if (!$async) {
            return $client->sendRequest($request);
        }

        $newResponse = null;
        $client->sendAsyncRequest($request, ['callback'=>function(RequestInterface $request, ResponseInterface $response = null, ClientException $exception = null) use (&$newResponse) {
            $newResponse = $response;
        }]);

        $client->flush();

        return $newResponse;
    }
}
