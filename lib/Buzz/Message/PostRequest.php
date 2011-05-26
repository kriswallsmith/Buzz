<?php

namespace Buzz\Message;

/**
 * PostRequest.
 *
 * @author Marc Weistroff <marc.weistroff@sensio.com>
 */
class PostRequest extends Request
{
    protected $fields = array();

    /**
     * __construct
     *
     * @param string $method
     * @param string $resource
     * @param string $host
     */
    public function __construct($method = self::METHOD_POST, $resource = '/', $host = null)
    {
        parent::__construct($method, $resource, $host);
    }

    public function addFormData($name, $value)
    {
        $this->fields[$name] = $value;
    }

    public function getFormData()
    {
        return $this->fields;
    }

    public function setContent($content)
    {
        throw new \BadMethodCallException('It is not permitted to set the content.');
    }

    /**
     * Generates the content of the request.
     *
     * @return string
     */
    public function getContent()
    {
        $content = '&'.http_build_query($this->fields);

        $this->content  = "Content-Type: application/x-www-form-urlencoded";
        $this->content .= "\r\n";
        $this->content .= sprintf('Content-Length: %d', strlen($content));
        $this->content .= "\r\n\r\n";
        $this->content .= $content;

        return $this->content;
    }
}
