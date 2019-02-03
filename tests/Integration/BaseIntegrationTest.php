<?php

declare(strict_types=1);

namespace Buzz\Test\Integration;

use PHPUnit\Framework\TestCase;

abstract class BaseIntegrationTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        if (getenv('BUZZ_TEST_SERVER')) {
            $_SERVER['BUZZ_TEST_SERVER'] = getenv('BUZZ_TEST_SERVER');
        }

        if (getenv('TEST_SERVER')) {
            $_SERVER['TEST_SERVER'] = getenv('TEST_SERVER');
        }
    }
}
