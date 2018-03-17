<?php

namespace Buzz\Test\Client;

use Buzz\Browser;
use Buzz\Client\BatchClientInterface;
use Buzz\Client\Curl;
use Buzz\Client\FileGetContents;
use Buzz\Client\MultiCurl;
use Buzz\Message\Form\FormUpload;
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
     * @group legacy
     */
    public function testRequestMethods($client, $method)
    {
        $request = new Request($method, $_SERVER['BUZZ_TEST_SERVER'], [], 'test');
        $response = $this->send($client, $request);

        $data = json_decode($response->getBody()->__toString(), true);

        $this->assertEquals($method, $data['SERVER']['REQUEST_METHOD']);
    }

    /**
     * @dataProvider provideClient
     * @group legacy
     */
    public function testGetContentType($client)
    {
        $request = new Request('GET', $_SERVER['BUZZ_TEST_SERVER']);
        $response = $this->send($client, $request);

        $data = json_decode($response->getBody()->__toString(), true);

        $this->assertArrayNotHasKey('CONTENT_TYPE', $data['SERVER']);
    }

    /**
     * @dataProvider provideClient
     * @group legacy
     */
    public function testFormPost($client)
    {
        $request = new Request(
            'POST',
            $_SERVER['BUZZ_TEST_SERVER'],
            ['Content-Type'=>'application/x-www-form-urlencoded'],
            http_build_url(['company[name]'=>'Google'])
        );
        $response = $this->send($client, $request);

        $data = json_decode($response->getBody()->__toString(), true);

        $this->assertStringStartsWith('application/x-www-form-urlencoded', $data['SERVER']['CONTENT_TYPE']);
        $this->assertEquals('Google', $data['POST']['company']['name']);
    }

    /**
     * @dataProvider provideClient
     */
    public function testFormPostWithRequestBuilder($client)
    {
        if ($client instanceof MultiCurl) {
            $this->markTestSkipped('Invalid input');
        }

        $builder = new FormRequestBuilder();
        $builder->addField('company[name]', 'Google');
        $browser = new Browser($client);
        $response = $browser->submitForm($_SERVER['BUZZ_TEST_SERVER'], $builder->build());

        $data = json_decode($response->getBody()->__toString(), true);

        $this->assertStringStartsWith('application/x-www-form-urlencoded', $data['SERVER']['CONTENT_TYPE']);
        $this->assertEquals('Google', $data['POST']['company']['name']);
    }

    /**
     * @group legacy
     */
    public function testMultiCurlExecutesRequestsConcurently()
    {
        $client = new MultiCurl();
        $client->setTimeout(10);

        $calls = array();
        $callback = function($client, $request, $response, $options, $error) use(&$calls) {
            $calls[] = func_get_args();
        };

        for ($i = 3; $i > 0; $i--) {
            $request = new Request('GET', $_SERVER['BUZZ_TEST_SERVER'].'?delay='.$i);
            $client->sendRequest($request, array('callback' => $callback));
        }

        $client->flush();
        $this->assertCount(3, $calls);
    }

    public function provideClient()
    {
        return array(
            array(new Curl()),
            array(new FileGetContents()),
            array(new MultiCurl()),
        );
    }

    public function provideClientAndMethod()
    {
        // HEAD is intentionally omitted
        // http://stackoverflow.com/questions/2603104/does-mod-php-honor-head-requests-properly

        $methods = array('GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS');
        $clients = $this->provideClient();

        $data = array();
        foreach ($clients as $client) {
            foreach ($methods as $method) {
                $data[] = array($client[0], $method);
            }
        }

        return $data;
    }

    private function send(ClientInterface $client, RequestInterface $request):  ResponseInterface
    {
        $response = $client->sendRequest($request);

        if ($client instanceof BatchClientInterface) {
            $client->flush();
        }

        return $response;
    }
}
