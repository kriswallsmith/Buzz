<?php

namespace Buzz\Message;

/**
 * FormRequest.
 *
 * @author Marc Weistroff <marc.weistroff@sensio.com>
 */
class FormRequest extends Request
{
    private $fields = array();

    /**
     * Constructor.
     *
     * Defaults to POST rather than GET.
     */
    public function __construct($method = self::METHOD_POST, $resource = '/', $host = null)
    {
        parent::__construct($method, $resource, $host);
    }

    public function setField($name, $value)
    {
        if (is_array($value)) {
            $this->addFields(array($name => $value));
            return;
        }

        $this->fields[$name] = $value;
    }

    public function addFields(array $fields)
    {
        foreach ($this->flattenArray($fields) as $name => $value) {
            $this->setField($name, $value);
        }
    }

    public function setFields(array $fields)
    {
        $this->fields = array();
        $this->addFields($fields);
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setContent($content)
    {
        throw new \BadMethodCallException('It is not permitted to set the content.');
    }

    public function getHeaders()
    {
        return array_merge(parent::getHeaders(), array(
            'Content-Type: application/x-www-form-urlencoded',
        ));
    }

    public function getContent()
    {
        return http_build_query($this->fields);
    }

    // private

    private function flattenArray(array $values, $prefix = '', $format = '%s')
    {
        $flat = array();

        foreach ($values as $name => $value) {
            $flatName = $prefix.sprintf($format, $name);

            if (is_array($value)) {
                $flat += $this->flattenArray($value, $flatName, '[%s]');
            } else {
                $flat[$flatName] = $value;
            }
        }

        return $flat;
    }
}
