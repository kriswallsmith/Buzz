<?php

declare(strict_types=1);

namespace Buzz\Util;

use Psr\Http\Message\RequestInterface;

class Cookie
{
    const ATTR_DOMAIN = 'domain';

    const ATTR_PATH = 'path';

    const ATTR_SECURE = 'secure';

    const ATTR_MAX_AGE = 'max-age';

    const ATTR_EXPIRES = 'expires';

    protected $name;

    protected $value;

    protected $attributes = [];

    protected $createdAt;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->createdAt = time();
    }

    /**
     * Returns true if the current cookie matches the supplied request.
     *
     * @param RequestInterface $request A request object
     *
     * @return bool
     */
    public function matchesRequest(RequestInterface $request): bool
    {
        $uri = $request->getUri();
        // domain
        if (!$this->matchesDomain($uri->getHost())) {
            return false;
        }

        // path
        if (!$this->matchesPath($uri->getPath())) {
            return false;
        }

        // secure
        if ($this->hasAttribute(static::ATTR_SECURE) && 'https' !== $uri->getScheme()) {
            return false;
        }

        return true;
    }

    /**
     * Returns true of the current cookie has expired.
     *
     * Checks the max-age and expires attributes.
     *
     * @return bool Whether the current cookie has expired
     */
    public function isExpired(): bool
    {
        $maxAge = $this->getAttribute(static::ATTR_MAX_AGE);
        if ($maxAge && time() - $this->getCreatedAt() > $maxAge) {
            return true;
        }

        $expires = $this->getAttribute(static::ATTR_EXPIRES);
        if ($expires && strtotime($expires) < time()) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the current cookie matches the supplied domain.
     *
     * @param string $domain A domain hostname
     *
     * @return bool
     */
    public function matchesDomain(string $domain): bool
    {
        $cookieDomain = $this->getAttribute(static::ATTR_DOMAIN);

        if (0 === strpos($cookieDomain, '.')) {
            $pattern = '/\b'.preg_quote(substr($cookieDomain, 1), '/').'$/i';

            return (bool) preg_match($pattern, $domain);
        } else {
            return 0 == strcasecmp($cookieDomain, $domain);
        }
    }

    /**
     * Returns true if the current cookie matches the supplied path.
     *
     * @param string $path A path
     *
     * @return bool
     */
    public function matchesPath(string $path): bool
    {
        $needle = $this->getAttribute(static::ATTR_PATH);

        return null === $needle || 0 === strpos($path, $needle);
    }

    /**
     * Populates the current cookie with data from the supplied Set-Cookie header.
     *
     * @param string $header        A Set-Cookie header
     * @param string $issuingDomain The domain that issued the header
     */
    public function fromSetCookieHeader(string $header, string $issuingDomain): void
    {
        list($this->name, $header) = explode('=', $header, 2);
        if (false === strpos($header, ';')) {
            $this->value = $header;
            $header = null;
        } else {
            list($this->value, $header) = explode(';', $header, 2);
        }

        $this->clearAttributes();
        if (null !== $header) {
            foreach (array_map('trim', explode(';', trim($header))) as $pair) {
                if (false === strpos($pair, '=')) {
                    $name = $pair;
                    $value = null;
                } else {
                    list($name, $value) = explode('=', $pair);
                }

                $this->setAttribute($name, $value);
            }
        }

        if (!$this->getAttribute(static::ATTR_DOMAIN)) {
            $this->setAttribute(static::ATTR_DOMAIN, $issuingDomain);
        }
    }

    /**
     * Formats a Cookie header for the current cookie.
     *
     * @return string An HTTP request Cookie header
     */
    public function toCookieHeader(): string
    {
        return $this->getName().'='.$this->getValue();
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setAttributes(array $attributes)
    {
        // attributes are case insensitive
        $this->attributes = array_change_key_case($attributes);
    }

    public function setAttribute(string $name, ?string $value): void
    {
        $this->attributes[strtolower($name)] = $value;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name): ?string
    {
        $name = strtolower($name);

        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }

        return null;
    }

    public function hasAttribute(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    public function clearAttributes(): void
    {
        $this->setAttributes([]);
    }

    public function setCreatedAt(int $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }
}
