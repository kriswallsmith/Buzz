<?php

namespace Buzz\Util;

use Buzz\Message\RequestInterface;

class Cookie
{
    const ATTR_DOMAIN  = 'domain';
    const ATTR_PATH    = 'path';
    const ATTR_SECURE  = 'secure';
    const ATTR_MAX_AGE = 'max-age';
    const ATTR_EXPIRES = 'expires';

    protected $name;
    protected $value;
    protected $attributes = array();
    protected $createdAt;

    public function __construct()
    {
        $this->createdAt = time();
    }

    /**
     * Returns true if the current cookie matches the supplied request.
     *
     * @return boolean
     */
    public function matchesRequest(RequestInterface $request)
    {
        // domain
        if (!$this->matchesDomain(parse_url($request->getHost(), PHP_URL_HOST))) {
            return false;
        }

        // path
        if (!$this->matchesPath($request->getResource())) {
            return false;
        }

        // secure
        if ($this->hasAttribute(static::ATTR_SECURE) && !$request->isSecure()) {
            return false;
        }

        return true;
    }

    /**
     * Returns true of the current cookie has expired.
     *
     * Checks the max-age and expires attributes.
     *
     * @return boolean Whether the current cookie has expired
     */
    public function isExpired()
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
     * @return boolean
     */
    public function matchesDomain($domain)
    {
        $cookieDomain = $this->getAttribute(static::ATTR_DOMAIN);

        if (0 === strpos($cookieDomain, '.')) {
            $pattern = '/\b'.preg_quote(substr($cookieDomain, 1), '/').'$/i';

            return (boolean) preg_match($pattern, $domain);
        } else {
            return 0 == strcasecmp($cookieDomain, $domain);
        }
    }

    /**
     * Returns true if the current cookie matches the supplied path.
     *
     * @param string $path A path
     *
     * @return boolean
     */
    public function matchesPath($path)
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
    public function fromSetCookieHeader($header, $issuingDomain)
    {
        list($this->name, $header)  = explode('=', $header, 2);
        if (false === strpos($header, ';')) {
            $this->value = $header;
            $header = null;
        } else {
            list($this->value, $header) = explode(';', $header, 2);
        }

        $this->clearAttributes();
        foreach (array_map('trim', explode(';', trim($header))) as $pair) {
            if (false === strpos($pair, '=')) {
                $name = $pair;
                $value = null;
            } else {
                list($name, $value) = explode('=', $pair);
            }

            $this->setAttribute($name, $value);
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
    public function toCookieHeader()
    {
        return 'Cookie: '.$this->getName().'='.$this->getValue();
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        // attributes are case insensitive
        $this->attributes = array_change_key_case($attributes);
    }

    /**
     * @param string $name  A name
     * @param mixed  $value A value
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[strtolower($name)] = $value;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getAttribute($name)
    {
        $name = strtolower($name);

        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * Clear the attributes
     */
    public function clearAttributes()
    {
        $this->setAttributes(array());
    }

    /**
     * @param int $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return int
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
