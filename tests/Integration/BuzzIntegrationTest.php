<?php

declare(strict_types=1);

namespace Buzz\Test\Integration;

use Buzz\Browser;
use Buzz\Client\AbstractCurl;
use Buzz\Client\BuzzClientInterface;
use Buzz\Client\Curl;
use Buzz\Client\FileGetContents;
use Buzz\Client\MultiCurl;
use Buzz\Exception\CallbackException;
use Buzz\Exception\ClientException;
use Buzz\Exception\NetworkException;
use Buzz\Message\FormRequestBuilder;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class BuzzIntegrationTest extends BaseIntegrationTest
{
    protected function setUp(): void
    {
        parent::setUp();
        if (empty($_SERVER['BUZZ_TEST_SERVER'])) {
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

        $this->assertEmpty($data['SERVER']['CONTENT_TYPE']);
    }

    /**
     * @dataProvider provideClient
     */
    public function testGetCurlInfo(BuzzClientInterface $client, bool $async)
    {
        if (!$client instanceof AbstractCurl) {
            $this->markTestSkipped('Only Curl supports this feature.');
        }

        $request = new Request('GET', $_SERVER['BUZZ_TEST_SERVER']);
        $response = $this->send($client, $request, $async, ['expose_curl_info' => true]);

        $this->assertTrue($response->hasHeader('__curl_info'));
        $curlInfo = json_decode($response->getHeader('__curl_info')[0], true);
        $this->assertArrayHasKey('total_time', $curlInfo);
        $this->assertGreaterThan(0, $curlInfo['total_time']);
    }

    /**
     * @dataProvider provideClient
     */
    public function testException($client, $async)
    {
        if ($async) {
            $this->markTestSkipped('Async clients should not throw exceptions');
        }
        $request = new Request('GET', $_SERVER['BUZZ_TEST_SERVER'].'?delay=3');

        $this->expectException(NetworkException::class);
        $client->sendRequest($request, ['timeout' => 1]);
    }

    /**
     * @dataProvider provideClient
     */
    public function testFormPost($client, $async)
    {
        $request = new Request(
            'POST',
            $_SERVER['BUZZ_TEST_SERVER'],
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            http_build_query(['company[name]' => 'Google'])
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
        $builder->addFile('image', __DIR__.'/../Resources/image.png', 'image/png', 'filename.png');
        $browser = new Browser($client, new Psr17Factory());
        $response = $browser->submitForm($_SERVER['BUZZ_TEST_SERVER'], $builder->build());

        $this->assertNotEmpty($response->getBody()->__toString(), 'Response from server should not be empty');

        $data = json_decode($response->getBody()->__toString(), true);
        $this->assertIsArray($data, $response->getBody()->__toString());
        $this->assertArrayHasKey('SERVER', $data);

        $this->assertStringStartsWith('multipart/form-data', $data['SERVER']['CONTENT_TYPE']);
        $this->assertEquals('Google', $data['POST']['company']['name']);
        $this->assertEquals('filename.png', $data['FILES']['image']['name']);
        $this->assertEquals('image/png', $data['FILES']['image']['type']);
        $this->assertEquals(39618, $data['FILES']['image']['size']);
    }

    /**
     * @dataProvider provideClient
     */
    public function testFormPostWithLargeRequestBody($client, $async)
    {
        if ($async) {
            $this->markTestSkipped('Skipping for async requests');
        }

        $browser = new Browser($client, new Psr17Factory());
        $response = $browser->submitForm($_SERVER['BUZZ_TEST_SERVER'], [
            'image' => [
                'path' => __DIR__.'/../Resources/large.png',
                'filename' => 'filename.png',
                'contentType' => 'image/png',
            ],
        ]);

        $this->assertNotEmpty($response->getBody()->__toString(), 'Response from server should not be empty');

        $data = json_decode($response->getBody()->__toString(), true);
        $this->assertIsArray($data, $response->getBody()->__toString());
        $this->assertArrayHasKey('SERVER', $data);

        $this->assertStringStartsWith('multipart/form-data', $data['SERVER']['CONTENT_TYPE']);
        $this->assertGreaterThan(39618, $data['FILES']['image']['size']);
    }

    public function testMultiCurlExecutesRequestsConcurrently()
    {
        $client = new MultiCurl(new Psr17Factory(), ['timeout' => 30]);

        $calls = [];
        $callback = function (RequestInterface $request, ResponseInterface $response = null, ClientException $exception = null) use (&$calls) {
            $calls[] = \func_get_args();
        };

        for ($i = 3; $i > 0; --$i) {
            $request = new Request('GET', $_SERVER['BUZZ_TEST_SERVER'].'?delay='.$i);
            $client->sendAsyncRequest($request, ['callback' => $callback]);
        }

        $client->flush();
        $this->assertCount(3, $calls);

        foreach ($calls as $i => $call) {
            /** @var ResponseInterface $response */
            $response = $call[1];
            $body = $response->getBody()->__toString();
            $array = json_decode($body, true);
            // Make sure the order is correct
            $this->assertEquals($i + 1, $array['GET']['delay']);
        }
    }

    /**
     * @dataProvider provideClient
     */
    public function testMaxFileSize(BuzzClientInterface $client, bool $async)
    {
        if (!$client instanceof AbstractCurl) {
            $this->markTestSkipped('Only Curl supports this feature.');
        }

        $curlOptions = [
            CURLOPT_NOPROGRESS => false,
            CURLOPT_PROGRESSFUNCTION => function ($curl, $downloadSize, $downloaded) {
                return 1;
            },
        ];

        $request = new Request('GET', $_SERVER['BUZZ_TEST_SERVER']);

        // MultiCurl async handles all exceptions in the callback.
        if ($async) {
            $response = $this->send($client, $request, $async, ['curl' => $curlOptions]);

            $this->assertNull($response);

            return;
        }

        $this->expectException(CallbackException::class);

        $this->send($client, $request, $async, ['curl' => $curlOptions]);
    }

    public function provideClient()
    {
        return [
            [new Curl(new Psr17Factory(), []), false],
            [new FileGetContents(new Psr17Factory(), []), false],
            [new MultiCurl(new Psr17Factory(), []), false],
            [new MultiCurl(new Psr17Factory(), []), true],
        ];
    }

    public function provideClientAndMethod()
    {
        // HEAD is intentionally omitted
        // http://stackoverflow.com/questions/2603104/does-mod-php-honor-head-requests-properly

        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'];
        $clients = $this->provideClient();

        foreach ($clients as $client) {
            foreach ($methods as $method) {
                yield [$client[0], $method, $client[1]];
            }
        }
    }

    private function send(BuzzClientInterface $client, RequestInterface $request, bool $async, array $options = []): ?ResponseInterface
    {
        if (!$async) {
            return $client->sendRequest($request, $options);
        }

        $newResponse = null;
        $options['callback'] = function (RequestInterface $request, ResponseInterface $response = null, ClientException $exception = null) use (&$newResponse) {
            $newResponse = $response;
        };
        $client->sendAsyncRequest($request, $options);

        $client->flush();

        return $newResponse;
    }
}
