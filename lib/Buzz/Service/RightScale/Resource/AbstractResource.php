<?php

namespace Buzz\Service\RightScale\Resource;

abstract class AbstractResource
{
  /**
   * Populates the current resource from a JSON-serialized data object.
   * 
   * @param string $json A JSON-serialized data object
   * 
   * @return AbstractResource
   */
  public function fromJson($json)
  {
    $this->fromArray(json_decode($json, true));
  }

  /**
   * Populates the current resource from an XML-serialized data object.
   * 
   * @param string $xml An XML-serialized data object
   * 
   * @return AbstractResource
   */
  public function fromXml($xml)
  {
    $toArray = function(\SimpleXMLElement $element) use (& $toArray)
    {
      $array = array();
      foreach ($element->children() as $child)
      {
        if (count($child->children()))
        {
          $array[] = $toArray($child);
        }
        else
        {
          // underscore
          $array[str_replace('-', '_', $child->getName())] = (string) $child;
        }
      }

      return $array;
    };

    $this->fromArray($toArray(new \SimpleXMLElement($xml)));
  }

  /**
   * Populates the current resource from an array.
   * 
   * @param array $array An array of values for the current object
   */
  abstract public function fromArray(array $array);
}
