<?php

namespace Buzz\Test\Message;

use Buzz\Message\Form\FormUpload;

class FormUploadTest extends \PHPUnit_Framework_TestCase
{
    public function testStringContent()
    {
        $upload = new FormUpload();
        $upload->setName('company[logo]');
        $upload->setFilename('google.png');
        $upload->setContent(file_get_contents(__DIR__.'/Fixtures/google.png'));

        $this->assertEquals(array(
            'Content-Disposition: form-data; name="company[logo]"; filename="google.png"',
            'Content-Type: image/png',
        ), $upload->getHeaders());
    }

    public function testFileContent()
    {
        $upload = new FormUpload(__DIR__.'/Fixtures/google.png');
        $upload->setName('company[logo]');

        $this->assertEquals(array(
            'Content-Disposition: form-data; name="company[logo]"; filename="google.png"',
            'Content-Type: image/png',
        ), $upload->getHeaders());
    }

    public function testContentType()
    {
        $upload = new FormUpload(__DIR__.'/Fixtures/google.png');
        $upload->setName('company[logo]');
        $upload->setContentType('foo/bar');

        $this->assertEquals(array(
            'Content-Disposition: form-data; name="company[logo]"; filename="google.png"',
            'Content-Type: foo/bar',
        ), $upload->getHeaders());
    }
}
