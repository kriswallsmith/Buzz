<?php

namespace Buzz\Test\Client;

use Buzz\Client\BatchClientInterface;
use Buzz\Client\ClientInterface;
use Buzz\Client\Curl;
use Buzz\Client\FileGetContents;
use Buzz\Client\MultiCurl;
use Buzz\Message\Form\FormRequest;
use Buzz\Message\Form\FormUpload;
use Buzz\Message\Request;
use Buzz\Message\RequestInterface;
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
        $request->setContent('test');
        $response = $this->send($client, $request);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals($method, $data['SERVER']['REQUEST_METHOD']);
    }

    /**
     * @dataProvider provideClient
     */
    public function testGetContentType($client)
    {
        $request = new Request();
        $request->fromUrl($_SERVER['TEST_SERVER']);
        $response = $this->send($client, $request);

        $data = json_decode($response->getContent(), true);

        $this->assertArrayNotHasKey('CONTENT_TYPE', $data['SERVER']);
    }

    /**
     * @dataProvider provideClient
     */
    public function testFormPost($client)
    {
        $request = new FormRequest();
        $request->fromUrl($_SERVER['TEST_SERVER']);
        $request->setField('company[name]', 'Google');
        $response = $this->send($client, $request);

        $data = json_decode($response->getContent(), true);

        $this->assertStringStartsWith('application/x-www-form-urlencoded', $data['SERVER']['CONTENT_TYPE']);
        $this->assertEquals('Google', $data['POST']['company']['name']);
    }

    /**
     * @dataProvider provideClient
     */
    public function testFormGet($client)
    {
        $request = new FormRequest(FormRequest::METHOD_GET);
        $request->fromUrl($_SERVER['TEST_SERVER']);
        $request->setField('search[query]', 'cats');
        $response = $this->send($client, $request);

        $data = json_decode($response->getContent(), true);

        $this->assertArrayNotHasKey('CONTENT_TYPE', $data['SERVER']);
        $this->assertEquals('cats', $data['GET']['search']['query']);
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
        $response = $this->send($client, $request);

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
        $request = new Request(RequestInterface::METHOD_POST);
        $request->fromUrl($_SERVER['TEST_SERVER']);
        $request->addHeader('Content-Type: application/json');
        $request->setContent(json_encode(array('foo' => 'bar')));
        $response = $this->send($client, $request);

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
        $request = new Request(RequestInterface::METHOD_PUT);
        $request->fromUrl($_SERVER['TEST_SERVER']);
        $request->addHeader('Content-Type: application/json');
        $request->setContent(json_encode(array('foo' => 'bar')));
        $response = $this->send($client, $request);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals('PUT', $data['SERVER']['REQUEST_METHOD']);
        $this->assertEquals('application/json', $data['SERVER']['CONTENT_TYPE']);
        $this->assertEquals('{"foo":"bar"}', $data['INPUT']);

        // request 2
        $request = new Request(RequestInterface::METHOD_GET);
        $request->fromUrl($_SERVER['TEST_SERVER']);
        $response = $this->send($client, $request);

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
        $response = $this->send($client, $request);

        $data = json_decode($response->getContent(), true);
        parse_str($data['INPUT'], $fields);

        $this->assertEquals(array('math' => '1+1=2'), $fields);
    }

    /**
     * @dataProvider provideClient
     */
    public function testRedirectedResponse($client)
    {
        $request = new Request();
        $request->fromUrl($_SERVER['TEST_SERVER'].'?redirect_to='.$_SERVER['TEST_SERVER']);
        $response = $this->send($client, $request);

        $headers = $response->getHeaders();
        $this->assertContains('200', $headers[0]);
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

    private function send(ClientInterface $client, RequestInterface $request)
    {
        $response = new Response();
        $client->send($request, $response);

        if ($client instanceof BatchClientInterface) {
            $client->flush();
        }

        return $response;
    }
}
