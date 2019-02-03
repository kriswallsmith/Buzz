<?php

declare(strict_types=1);

namespace Buzz\Client;

use Buzz\Configuration\ParameterBag;
use Buzz\Exception\InvalidArgumentException;
use Http\Message\ResponseFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractClient
{
    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * @var ParameterBag
     */
    private $options;

    /**
     * @var ResponseFactoryInterface|ResponseFactory
     */
    protected $responseFactory;

    /**
     * @param ResponseFactoryInterface|ResponseFactory $responseFactory
     */
    public function __construct($responseFactory, array $options = [])
    {
        if (!$responseFactory instanceof ResponseFactoryInterface && !$responseFactory instanceof ResponseFactory) {
            throw new InvalidArgumentException(sprintf('First argument of %s must be an instance of %s or %s.', __CLASS__, ResponseFactoryInterface::class, ResponseFactory::class));
        }

        $this->options = new ParameterBag();
        $this->options = $this->doValidateOptions($options);
        $this->responseFactory = $responseFactory;
    }

    protected function getOptionsResolver(): OptionsResolver
    {
        if (null !== $this->optionsResolver) {
            return $this->optionsResolver;
        }

        $this->optionsResolver = new OptionsResolver();
        $this->configureOptions($this->optionsResolver);

        return $this->optionsResolver;
    }

    /**
     * Validate a set of options and return a new and shiny ParameterBag.
     */
    protected function validateOptions(array $options = []): ParameterBag
    {
        if (empty($options)) {
            return $this->options;
        }

        return $this->doValidateOptions($options);
    }

    /**
     * Validate a set of options and return a new and shiny ParameterBag.
     */
    private function doValidateOptions(array $options = []): ParameterBag
    {
        $parameterBag = $this->options->add($options);

        try {
            $parameters = $this->getOptionsResolver()->resolve($parameterBag->all());
        } catch (\Throwable $e) {
            // Wrap any errors.
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }

        return new ParameterBag($parameters);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_redirects' => false,
            'max_redirects' => 5,
            'timeout' => 0,
            'verify' => true,
            'proxy' => null,
        ]);

        $resolver->setAllowedTypes('allow_redirects', 'boolean');
        $resolver->setAllowedTypes('verify', 'boolean');
        $resolver->setAllowedTypes('max_redirects', 'integer');
        $resolver->setAllowedTypes('timeout', ['integer', 'float']);
        $resolver->setAllowedTypes('proxy', ['null', 'string']);
    }
}
