<?php

declare(strict_types=1);

namespace Buzz\Configuration;

/**
 * A ParameterBag is a container for key/value pairs. This implementation is immutable.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class ParameterBag implements \IteratorAggregate, \Countable
{
    /**
     * Parameter storage.
     */
    private $parameters;

    /**
     * @param array $parameters An array of parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * Returns the parameters.
     *
     * @return array An array of parameters
     */
    public function all(): array
    {
        return $this->parameters;
    }

    /**
     * Returns the parameter keys.
     *
     * @return array An array of parameter keys
     */
    public function keys(): array
    {
        return array_keys($this->parameters);
    }

    /**
     * Adds parameters to the existing ones.
     *
     * @param array $parameters An array of parameters
     */
    public function add(array $parameters = []): self
    {
        // Make sure to merge Curl parameters
        if (isset($this->parameters['curl'])
            && isset($parameters['curl'])
            && \is_array($this->parameters['curl'])
            && \is_array($parameters['curl'])) {
            $parameters['curl'] = array_replace($this->parameters['curl'], $parameters['curl']);
        }

        $newParameters = array_replace($this->parameters, $parameters);

        return new self($newParameters);
    }

    /**
     * Returns a parameter by name.
     *
     * @param string|int $key     The key
     * @param mixed      $default The default value if the parameter key does not exist
     */
    public function get($key, $default = null)
    {
        return \array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;
    }

    /**
     * Returns true if the parameter is defined.
     *
     * @param string|int $key The key
     *
     * @return bool true if the parameter exists, false otherwise
     */
    public function has($key): bool
    {
        return \array_key_exists($key, $this->parameters);
    }

    /**
     * Returns an iterator for parameters.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->parameters);
    }

    /**
     * Returns the number of parameters.
     *
     * @return int The number of parameters
     */
    public function count(): int
    {
        return \count($this->parameters);
    }
}
