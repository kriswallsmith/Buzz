<?php

namespace Buzz\Message;

/**
 * FormRequestTest
 *
 * @author Marc Weistroff <marc.weistroff@sensio.com>
 */
class FormRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testGetContentGeneratesContent()
    {
        $message = new FormRequest();
        $message->addFormData('foo', 'bar');
        $message->addFormData('bar', 'foo');

        $expected = "foo=bar&bar=foo";
        $this->assertEquals($expected, $message->getContent());
    }

    public function testAddDataAddsData()
    {
        $message = new FormRequest();
        $message->addFormData('foo', 'bar');

        $this->assertEquals(array('foo' => 'bar'), $message->getFormData());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testSetContentIsNotPermitted()
    {
        $message = new FormRequest();
        $message->setContent('foobar');
    }
}
