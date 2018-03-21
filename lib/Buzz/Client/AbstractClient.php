<?php

declare(strict_types=1);

namespace Buzz\Client;

use Buzz\Exception\InvalidArgumentException;
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

    public function __construct(array $options = [])
    {
        $this->options = new ParameterBag($options);
        $this->validateOptions();
    }

    protected function getOptionsResolver()
    {
        if (null !== $this->optionsResolver) {
            return $this->optionsResolver;
        }

        $this->optionsResolver = new OptionsResolver();
        $this->configureOptions($this->optionsResolver);
    }

    /**
     * Validate a set of options and return a new and shiny ParameterBag.
     */
    protected function validateOptions(array $options = []): ParameterBag
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

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'follow_redirects' => false,
            'max_redirects' => 5,
            'timeout' => 30,
            'verify_peer' => true,
            'verify_host' => true,
            'proxy' => null,
        ]);

        $resolver->setAllowedTypes('follow_redirects', 'boolean');
        $resolver->setAllowedTypes('verify_peer', 'boolean');
        $resolver->setAllowedTypes('verify_host', 'boolean');
        $resolver->setAllowedTypes('max_redirects', 'integer');
        $resolver->setAllowedTypes('timeout', 'integer');
        $resolver->setAllowedTypes('timeout', 'float');
        $resolver->setAllowedTypes('proxy', ['null', 'string']);
    }

    protected function parseStatusLine(string $statusLine): array
    {
        $protocolVersion = null;
        $statusCode = 0;
        $reasonPhrase = null;

        if (2 <= count($parts = explode(' ', $statusLine, 3))) {
            $protocolVersion = (string) substr($parts[0], 5);
            $statusCode = (int) $parts[1];
            $reasonPhrase = isset($parts[2]) ? $parts[2] : '';
        }

        return [$protocolVersion, $statusCode, $reasonPhrase];
    }
}
