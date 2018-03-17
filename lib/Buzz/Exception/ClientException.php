<?php

declare(strict_types=1);

namespace Buzz\Exception;

use Psr\Http\Message\RequestInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ClientException extends \RuntimeException implements ExceptionInterface
{

}
