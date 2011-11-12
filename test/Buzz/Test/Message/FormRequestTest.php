<?php

namespace Buzz\Test\Message;

use Buzz\Message\FormRequest;

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
        $message->setField('foo', 'bar');
        $message->setField('bar', 'foo');

        $expected = "foo=bar&bar=foo";
        $this->assertEquals($expected, $message->getContent());
    }

    public function testAddDataAddsData()
    {
        $message = new FormRequest();
        $message->setField('foo', 'bar');

        $this->assertEquals(array('foo' => 'bar'), $message->getFields());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testSetContentIsNotPermitted()
    {
        $message = new FormRequest();
        $message->setContent('foobar');
    }

    public function testSetFields()
    {
        $request = new FormRequest();
        $request->setFields(array('foo' => 'bar'));
        $this->assertEquals(array('foo' => 'bar'), $request->getFields());
    }

    public function testContentType()
    {
        $request = new FormRequest();
        $this->assertEquals('application/x-www-form-urlencoded', $request->getHeader('Content-Type'));
    }

    public function testDeepArray()
    {
        $request = new FormRequest();
        $request->setField('person', array('fname' => 'John', 'lname' => 'Doe'));
        $this->assertEquals(array('person[fname]' => 'John', 'person[lname]' => 'Doe'), $request->getFields());
        $this->assertEquals('person%5Bfname%5D=John&person%5Blname%5D=Doe', $request->getContent());
    }
}
