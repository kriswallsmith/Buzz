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
        $message->addFormData('bar', 'foo');

        $expected = "foo=bar&bar=foo";
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
