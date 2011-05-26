<?php

namespace Buzz\Message;

/**
 * PostRequestTest
 *
 * @author Marc Weistroff <marc.weistroff@sensio.com>
 */
class PostRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testGetContentGeneratesContent()
    {
        $message = new PostRequest();
        $message->addFormData('foo', 'bar');

        $expected = "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: 8\r\n\r\n&foo=bar";
        $this->assertEquals($expected, $message->getContent());
    }

    public function testAddDataAddsData()
    {
        $message = new PostRequest();
        $message->addFormData('foo', 'bar');

        $this->assertEquals(array('foo' => 'bar'), $message->getFormData());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testSetContentIsNotPermitted()
    {
        $message = new PostRequest();
        $message->setContent('foobar');
    }
}
