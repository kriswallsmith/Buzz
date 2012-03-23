<?php

namespace Buzz\Test\Client;

use Buzz\Client\Curl;
use Buzz\Client\FileGetContents;
use Buzz\Message\FormRequest;
use Buzz\Message\FormUpload;
use Buzz\Message\Request;
use Buzz\Message\Response;

class FunctionalTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!isset($_SERVER['TEST_SERVER'])) {
            $this->markTestSkipped('The test server is not configured.');
        }
    }

    /**
     * @dataProvider provideClientAndMethod
     */
    public function testRequestMethods($client, $method)
    {
        $request = new Request($method);
        $request->fromUrl($_SERVER['TEST_SERVER']);
        $response = new Response();
        $client->send($request, $response);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals($method, $data['SERVER']['REQUEST_METHOD']);
    }

    /**
     * @dataProvider provideClient
     */
    public function testFormPost($client)
    {
        $request = new FormRequest();
        $request->fromUrl($_SERVER['TEST_SERVER']);
        $request->setField('company[name]', 'Google');
        $response = new Response();
        $client->send($request, $response);

        $data = json_decode($response->getContent(), true);

        $this->assertStringStartsWith('application/x-www-form-urlencoded', $data['SERVER']['CONTENT_TYPE']);
        $this->assertEquals('Google', $data['POST']['company']['name']);
    }

    /**
     * @dataProvider provideClientAndUpload
     */
    public function testFileUpload($client, $upload)
    {
        $request = new FormRequest();
        $request->fromUrl($_SERVER['TEST_SERVER']);
        $request->setField('company[name]', 'Google');
        $request->setField('company[logo]', $upload);
        $response = new Response();
        $client->send($request, $response);

        $data = json_decode($response->getContent(), true);

        $this->assertStringStartsWith('multipart/form-data', $data['SERVER']['CONTENT_TYPE']);
        $this->assertEquals('Google', $data['POST']['company']['name']);
        $this->assertEquals('google.png', $data['FILES']['company']['name']['logo']);
    }

    /**
     * @dataProvider provideClient
     */
    public function testJsonPayload($client)
    {
        $request = new Request(Request::METHOD_POST);
        $request->fromUrl($_SERVER['TEST_SERVER']);
        $request->addHeader('Content-Type: application/json');
        $request->setContent(json_encode(array('foo' => 'bar')));
        $response = new Response();
        $client->send($request, $response);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals('application/json', $data['SERVER']['CONTENT_TYPE']);
        $this->assertEquals('{"foo":"bar"}', $data['INPUT']);
    }

    /**
     * @dataProvider provideClient
     */
    public function testConsecutiveRequests($client)
    {
        // request 1
        $request = new Request(Request::METHOD_PUT);
        $request->fromUrl($_SERVER['TEST_SERVER']);
        $request->addHeader('Content-Type: application/json');
        $request->setContent(json_encode(array('foo' => 'bar')));
        $response = new Response();
        $client->send($request, $response);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals('PUT', $data['SERVER']['REQUEST_METHOD']);
        $this->assertEquals('application/json', $data['SERVER']['CONTENT_TYPE']);
        $this->assertEquals('{"foo":"bar"}', $data['INPUT']);

        // request 2
        $request = new Request(Request::METHOD_GET);
        $request->fromUrl($_SERVER['TEST_SERVER']);
        $response = new Response();
        $client->send($request, $response);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals('GET', $data['SERVER']['REQUEST_METHOD']);
        $this->assertEmpty($data['INPUT']);
    }

    /**
     * @dataProvider provideClient
     */
    public function testPlus($client)
    {
        $request = new FormRequest();
        $request->fromUrl($_SERVER['TEST_SERVER']);
        $request->setField('math', '1+1=2');
        $response = new Response();
        $client->send($request, $response);

        $data = json_decode($response->getContent(), true);
        parse_str($data['INPUT'], $fields);

        $this->assertEquals(array('math' => '1+1=2'), $fields);
    }

    public function provideClient()
    {
        return array(
            array(new Curl()),
            array(new FileGetContents()),
        );
    }

    public function provideClientAndMethod()
    {
        // HEAD is intentionally omitted
        // http://stackoverflow.com/questions/2603104/does-mod-php-honor-head-requests-properly

        $methods = array('GET', 'POST', 'PUT', 'DELETE', 'PATCH');
        $clients = $this->provideClient();

        $data = array();
        foreach ($clients as $client) {
            foreach ($methods as $method) {
                $data[] = array($client[0], $method);
            }
        }

        return $data;
    }

    public function provideClientAndUpload()
    {
        $stringUpload = new FormUpload();
        $stringUpload->setFilename('google.png');
        $stringUpload->setContent(file_get_contents(__DIR__.'/../Message/Fixtures/google.png'));

        $uploads = array($stringUpload, new FormUpload(__DIR__.'/../Message/Fixtures/google.png'));
        $clients = $this->provideClient();

        $data = array();
        foreach ($clients as $client) {
            foreach ($uploads as $upload) {
                $data[] = array($client[0], $upload);
            }
        }

        return $data;
    }
}
