<?php

namespace Buzz\Test\Message;

use Buzz\Message\Form\FormRequest;
use Buzz\Message\Form\FormUpload;

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

        $this->assertEquals('person%5Bfname%5D=John&person%5Blname%5D=Doe', $request->getContent());
    }

    public function testFieldPush()
    {
        $request = new FormRequest();
        $request->setField('colors[]', 'red');
        $request->setField('colors[]', 'blue');

        $this->assertEquals('colors%5B0%5D=red&colors%5B1%5D=blue', $request->getContent());
    }

    public function testMultipartHeaders()
    {
        $request = new FormRequest();
        $request->setField('foo', array('bar' => new FormUpload()));

        $headers = $request->getHeaders();

        $this->assertStringStartsWith('Content-Type: multipart/form-data; boundary=', $headers[0]);
    }

    public function testMultipartContent()
    {
        $upload = new FormUpload();
        $upload->setFilename('image.jpg');
        $upload->setContent('foobar');

        $request = new FormRequest();
        $request->setField('user[name]', 'Kris');
        $request->setField('user[image]', $upload);

        $content = $request->getContent();

        $this->assertContains("Content-Disposition: form-data; name=\"user[name]\"\r\n\r\nKris\r\n", $content);
        $this->assertContains("Content-Disposition: form-data; name=\"user[image]\"; filename=\"image.jpg\"\r\nContent-Type: text/plain\r\n\r\nfoobar\r\n", $content);
    }

    public function testFilenamelessUpload()
    {
        $this->setExpectedException('LogicException');

        $upload = new FormUpload();
        $upload->setContent('foobar');

        $request = new FormRequest();
        $request->setField('user[name]', 'Kris');
        $request->setField('user[image]', $upload);

        $content = $request->getContent();
    }

    public function testGetRequest()
    {
        $request = new FormRequest(FormRequest::METHOD_GET, '/search');
        $request->setField('q', 'cats');

        $this->assertEquals('/search?q=cats', $request->getResource());
        $this->assertEmpty($request->getContent());
    }
}
