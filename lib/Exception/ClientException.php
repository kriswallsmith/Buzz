<?php

declare(strict_types=1);

namespace Buzz\Exception;

use Http\Client\Exception as HTTPlugException;
use Psr\Http\Client\ClientExceptionInterface as PsrClientException;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ClientException extends \RuntimeException implements ExceptionInterface, HTTPlugException, PsrClientException
{
}
