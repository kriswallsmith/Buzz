<?php

declare(strict_types=1);

namespace Buzz\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentTypeMiddleware implements MiddlewareInterface
{
    /**
     * Allow to disable the content type detection when stream is too large (as it can consume a lot of resource).
     *
     * @var bool
     *
     * true     skip the content type detection
     * false    detect the content type (default value)
     */
    protected $skipDetection;

    /**
     * Determine the size stream limit for which the detection as to be skipped (default to 16Mb).
     *
     * @var int
     */
    protected $sizeLimit;

    /**
     * @param array $config {
     *
     *     @var bool $skip_detection True skip detection if stream size is bigger than $size_limit
     *     @var int $size_limit size stream limit for which the detection as to be skipped.
     * }
     */
    public function __construct(array $config = [])
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'skip_detection' => false,
            'size_limit' => 16000000,
        ]);
        $resolver->setAllowedTypes('skip_detection', 'bool');
        $resolver->setAllowedTypes('size_limit', 'int');
        $options = $resolver->resolve($config);
        $this->skipDetection = $options['skip_detection'];
        $this->sizeLimit = $options['size_limit'];
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next)
    {
        if ($this->skipDetection || $request->hasHeader('Content-Type')) {
            return $next($request);
        }

        $stream = $request->getBody();
        $streamSize = $stream->getSize();

        if (empty($streamSize) || $streamSize >= $this->sizeLimit || !$stream->isSeekable()) {
            return $next($request);
        }

        if ($this->isJson($stream)) {
            $request = $request->withHeader('Content-Type', 'application/json');

            return $next($request);
        } elseif ($this->isXml($stream)) {
            $request = $request->withHeader('Content-Type', 'application/xml');

            return $next($request);
        }

        return $next($request);
    }

    /**
     * {@inheritdoc}
     */
    public function handleResponse(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        return $next($request, $response);
    }

    private function isJson(StreamInterface $stream): bool
    {
        $stream->rewind();
        json_decode($stream->getContents());

        return JSON_ERROR_NONE === json_last_error();
    }

    private function isXml(StreamInterface $stream): bool
    {
        $stream->rewind();
        $previousValue = libxml_use_internal_errors(true);
        $isXml = simplexml_load_string($stream->getContents());
        libxml_use_internal_errors($previousValue);

        return false !== $isXml;
    }
}
