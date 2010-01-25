<?php

namespace Buzz;

class Cookie
{
  protected $name;
  protected $value;
  protected $attributes = array();

  public function fromSetCookieHeader($header)
  {
    list($this->name, $header)  = explode('=', $header, 2);
    list($this->value, $header) = explode(';', $header, 2);

    $attributes = array();
    foreach (array_map('trim', explode(';', trim($header))) as $pair)
    {
      list($name, $value) = explode('=', $pair);
      $attributes[$name] = $value;
    }
    $this->setAttributes($attributes);
  }

  public function toCookieHeader()
  {
    return 'Cookie: '.$this->getName().'='.$this->getValue();
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function getName()
  {
    return $this->name;
  }

  public function setValue($value)
  {
    $this->value = $value;
  }

  public function getValue()
  {
    return $this->value;
  }

  public function setAttributes(array $attributes)
  {
    $this->attributes = $attributes;
  }

  public function getAttributes()
  {
    return $this->attributes;
  }
}
