<?php
declare(strict_types=1);

namespace Buzz\Client;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
interface BuzzClientInterface extends ClientInterface
{
    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request, array $options = []): ResponseInterface;
}
