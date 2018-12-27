<?php

declare(strict_types=1);

namespace Buzz\Test\Unit\Exception;

use Buzz\Exception\ClientException;
use Buzz\Exception\ExceptionInterface;
use Buzz\Exception\InvalidArgumentException;
use Buzz\Exception\LogicException;
use Buzz\Exception\NetworkException;
use Buzz\Exception\RequestException;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;

class InitializeExceptionTest extends TestCase
{
    public function testInitialize()
    {
        $e[] = new ClientException();
        $e[] = new InvalidArgumentException();
        $e[] = new LogicException();
        $e[] = new NetworkException(new Request('GET', '/'));
        $e[] = new RequestException(new Request('GET', '/'));

        foreach ($e as $exception) {
            $this->assertInstanceOf(ExceptionInterface::class, $exception);
        }
    }
}