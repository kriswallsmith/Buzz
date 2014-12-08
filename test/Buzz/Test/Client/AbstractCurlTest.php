<?php

namespace Buzz\Test\Client;

use Buzz\Client\AbstractCurl;

class AbstractCurlTest extends \PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $pseudoInstance = $this->getMockForAbstractClass(
            'Buzz\Client\AbstractCurl'
        );

        $this->assertStringStartsWith(
            'Mock_AbstractCurl',
            get_class($pseudoInstance)
        );
    }

    public function testSetOption()
    {
        /**
         * @var AbstractCurl $pseudoInstance
         */
        $pseudoInstance = $this->getMockForAbstractClass(
            'Buzz\Client\AbstractCurl'
        );

        $pseudoInstance->setOption('lorem', 'ipsum');
        $options = $this->readAttribute($pseudoInstance, 'options');

        $this->assertEquals(
            'ipsum',
            $options['lorem']
        );

        $pseudoInstance->setOption('lorem', null);
        $options = $this->readAttribute($pseudoInstance, 'options');

        $this->assertFalse(isset($options['lorem']));
    }
} 
