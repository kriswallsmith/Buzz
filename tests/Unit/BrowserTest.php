<?php

namespace Buzz\Test\Unit;

use Buzz\Browser;
use Buzz\Client\Curl;

use Nyholm\Psr7\Factory\MessageFactory;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class BrowserTest extends TestCase
{
    /** @var Curl */
    private $client;

    /** @var Browser */
    private $browser;

    protected function setUp()
    {
        $this->client = $this->getMockBuilder('Buzz\Client\Curl')->getMock();

        $this->browser = new Browser($this->client);
    }

    /**
     * @dataProvider provideMethods
     */
    public function testBasicMethods($method, $content)
    {
        $response = new Response(200, [], 'foobar');
        $headers = ['X-Foo'=>'bar'];

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue($response));

        $actual = $this->browser->$method('http://google.com/', $headers, $content);

        $this->assertInstanceOf(ResponseInterface::class, $actual);
        $this->assertEquals($response->getBody()->__toString(), $actual->getBody()->__toString());
    }

    public function provideMethods()
    {
        return array(
            array('get',    ''),
            array('head',   ''),
            array('post',   'content'),
            array('put',    'content'),
            array('delete', 'content'),
        );
    }

    public function testLastMessages()
    {
        $request = new Request('GET', 'http://www.google.se');
        $response = new Response(200, [], 'foobar');

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue($response));

        $this->browser->sendRequest($request);

        $this->assertSame($request, $this->browser->getLastRequest());
        $this->assertSame($response, $this->browser->getLastResponse());
    }

    /**
     * @dataProvider submitFormProvider
     */
    public function testSubmitForm(array $fields, array $headers, array $requestHeaders, string $requestBody)
    {

        $request = new Request('GET', '/');
        $response = new Response(201);
        $headerValidator = function (array $input) use ($requestHeaders) {
            foreach ($requestHeaders as $name => $value) {
                if (!isset($input[$name])) {
                    return false;
                }

                if ('regex' === substr($value, 0, 5)) {
                    if (!preg_match(substr($value, 5), $input[$name])) {
                        return false;
                    }
                } elseif ($value !== $input[$name]) {
                    return false;
                }
            }

            return true;
        };
        $bodyValidator = function ($input) use ($requestBody) {
            if ('regex' !== substr($requestBody, 0, 5)) {
                return $input === $requestBody;
            }
            $regex = substr($requestBody, 5);
            $regex = str_replace("\n", "\r\n", $regex);

            return preg_match($regex, $input);
        };

        $messageFactory = $this->getMockBuilder(MessageFactory::class)
            ->setMethods(['createRequest'])
            ->disableOriginalConstructor()
            ->getMock();

        $messageFactory->expects($this->once())->method('createRequest')
            ->with('POST', '/', $this->callback($headerValidator), $this->callback($bodyValidator))
            ->willReturn($request);

        $browser = $this->getMockBuilder(Browser::class)
            ->setMethods(['sendRequest', 'getMessageFactory'])
            ->disableOriginalConstructor()
            ->getMock();
        $browser->expects($this->once())->method('getMessageFactory')
            ->willReturn($messageFactory);
        $browser->expects($this->once())->method('sendRequest')
            ->willReturn($response);

        /** @var Browser $browser */
        $result = $browser->submitForm('/', $fields, 'POST', $headers);

        $this->assertEquals($response->getStatusCode(), $result->getStatusCode());
    }

    public function submitFormProvider()
    {
        yield [
            ['user[name]' => 'Kris Wallsmith', 'user[email]' => 'foo@bar.com'],
            ['foo'=>'bar'],
            ['foo'=>'bar', 'Content-Type'=>'application/x-www-form-urlencoded'],
            'user%5Bname%5D=Kris+Wallsmith&user%5Bemail%5D=foo%40bar.com'
        ];
        yield [
            ['email' => 'foo@bar.com', 'image' => ['path'=>__DIR__.'/Resources/pixel.gif']],
            [],
            ['Content-Type'=>'regex|^multipart/form-data; boundary=".+"$|'],
            'regex|--[0-9a-f\.]+
Content-Disposition: form-data; name="image"
Content-Length: 43

GIF[^;]+;
--[0-9a-f\.]+
Content-Disposition: form-data; name="email"
Content-Length: 11

foo@bar.com
--[0-9a-f\.]+--
|'];
        yield [
            ['email' => 'foo@bar.com', 'image' => [
                'path'=>__DIR__.'/Resources/pixel.gif',
                'contentType'=> 'image/gif',
                'filename'=> 'my-pixel.gif',
            ], 'other-image' => [
                'path'=>__DIR__.'/Resources/pixel.gif',
                'contentType'=> 'image/gif',
                'filename'=> 'other-pixel.gif',
            ]],
            [],
            ['Content-Type'=>'regex|^multipart/form-data; boundary=".+"$|'],
            'regex|--[0-9a-f\.]+
Content-Disposition: form-data; name="image"; filename="my-pixel.gif"
Content-Length: 43
Content-Type: image/gif

GIF[^;]+;
--[0-9a-f\.]+
Content-Disposition: form-data; name="other-image"; filename="other-pixel.gif"
Content-Length: 43
Content-Type: image/gif

GIF[^;]+;
--[0-9a-f\.]+
Content-Disposition: form-data; name="email"
Content-Length: 11

foo@bar.com
--[0-9a-f\.]+--
|'];
    }
}
