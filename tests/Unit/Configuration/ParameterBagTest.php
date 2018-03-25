<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Buzz\Test\Unit\Configuration;

use Buzz\Configuration\ParameterBag;
use PHPUnit\Framework\TestCase;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ParameterBagTest extends TestCase
{
    public function testConstructor()
    {
        $this->testAll();
    }

    public function testAll()
    {
        $bag = new ParameterBag(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $bag->all(), '->all() gets all the input');
    }

    public function testKeys()
    {
        $bag = new ParameterBag(['foo' => 'bar']);
        $this->assertEquals(['foo'], $bag->keys());
    }

    public function testAdd()
    {
        $bag = new ParameterBag(['foo' => 'bar']);
        $newBag = $bag->add(['bar' => 'bas']);
        $this->assertEquals(['foo' => 'bar', 'bar' => 'bas'], $newBag->all());
        $this->assertEquals(['foo' => 'bar'], $bag->all());
    }

    public function testAddCurl()
    {
        $bag = new ParameterBag(['foo' => 'bar', 'curl' => [1 => 'foo', 2 => 'bar']]);
        $newBag = $bag->add(['curl' => [2 => 'updated', 3 => 'biz']]);
        $this->assertEquals(['foo' => 'bar', 'curl' => [1 => 'foo', 2 => 'updated', 3 => 'biz']], $newBag->all());

        $bag = new ParameterBag(['foo' => 'bar']);
        $newBag = $bag->add(['curl' => [2 => 'updated', 3 => 'biz']]);
        $this->assertEquals(['foo' => 'bar', 'curl' => [2 => 'updated', 3 => 'biz']], $newBag->all());
    }

    public function testGet()
    {
        $bag = new ParameterBag(['foo' => 'bar', 'null' => null]);

        $this->assertEquals('bar', $bag->get('foo'), '->get() gets the value of a parameter');
        $this->assertEquals('default', $bag->get('unknown', 'default'), '->get() returns second argument as default if a parameter is not defined');
        $this->assertNull($bag->get('null', 'default'), '->get() returns null if null is set');
    }

    public function testGetDoesNotUseDeepByDefault()
    {
        $bag = new ParameterBag(['foo' => ['bar' => 'moo']]);

        $this->assertNull($bag->get('foo[bar]'));
    }

    public function testHas()
    {
        $bag = new ParameterBag(['foo' => 'bar']);

        $this->assertTrue($bag->has('foo'), '->has() returns true if a parameter is defined');
        $this->assertFalse($bag->has('unknown'), '->has() return false if a parameter is not defined');
    }

    public function testGetIterator()
    {
        $parameters = ['foo' => 'bar', 'hello' => 'world'];
        $bag = new ParameterBag($parameters);

        $i = 0;
        foreach ($bag as $key => $val) {
            ++$i;
            $this->assertEquals($parameters[$key], $val);
        }

        $this->assertEquals(count($parameters), $i);
    }

    public function testCount()
    {
        $parameters = ['foo' => 'bar', 'hello' => 'world'];
        $bag = new ParameterBag($parameters);

        $this->assertCount(count($parameters), $bag);
    }
}
