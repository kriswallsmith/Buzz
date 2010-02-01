<?php

namespace Buzz\Service\RightScale;

/**
 * A collection of tags.
 * 
 * Provides an interface for performing actions on multiple tags.
 */
class TagCollection extends Tag implements \Iterator, \ArrayAccess, \Countable
{
  protected $tags = array();

  /**
   * @see AbstractResource
   */
  public function fromArray(array $array)
  {
    foreach ($array as $data)
    {
      $tag = new Tag($this->getAPI());
      $tag->fromArray($data);

      $this->addTag($tag);
    }
  }

  // tags

  public function setTag($name, Tag $tag)
  {
    $this->tags[$name] = $tag;
  }

  public function setTags(array $tags)
  {
    $this->tags = $tags;
  }

  public function getTag($name)
  {
    if (isset($this->tags[$name]))
    {
      return $this->tags[$name];
    }
  }

  public function getTags()
  {
    return $this->tags;
  }

  public function hasTag($name)
  {
    return isset($this->tags[$name]);
  }

  public function addTag(Tag $tag)
  {
    $this->tags[] = $tag;
  }

  public function addTags($tags)
  {
    foreach ($tags as $tag)
    {
      $this->addTag($tag);
    }
  }

  public function removeTag($name)
  {
    unset($this->tags[$name]);
  }

  // ArrayAccess

  public function offsetSet($name, $value)
  {
    if (null === $name)
    {
      $this->addTag($value);
    }
    else
    {
      $this->setTag($name, $value);
    }
  }

  public function offsetGet($name)
  {
    return $this->getTag($name);
  }

  public function offsetExists($name)
  {
    return $this->hasTag($name);
  }

  public function offsetUnset($name)
  {
    $this->removeTag($name);
  }

  // Iterator

  public function key()
  {
    return key($this->tags);
  }

  public function current()
  {
    return current($this->tags);
  }

  public function next()
  {
    return next($this->tags);
  }

  public function rewind()
  {
    return reset($this->tags);
  }

  public function valid()
  {
    return false !== current($this->tags);
  }

  // Countable

  public function count()
  {
    return count($this->tags);
  }
}
