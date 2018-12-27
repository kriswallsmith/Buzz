<?php

declare(strict_types=1);

namespace Buzz\Test\Unit\Exception;

use Buzz\Exception as BuzzException;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;

class InitializeExceptionTest extends TestCase
{
    public function testInitialize()
    {
        $e[] = new BuzzException\ClientException();
        $e[] = new BuzzException\InvalidArgumentException();
        $e[] = new BuzzException\LogicException();
        $e[] = new BuzzException\NetworkException(new Request('GET', '/'));
        $e[] = new BuzzException\RequestException(new Request('GET', '/'));

        foreach ($e as $exception) {
            $this->assertInstanceOf(BuzzException\ExceptionInterface::class, $exception);
        }
    }
}
