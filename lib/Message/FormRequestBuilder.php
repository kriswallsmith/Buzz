<?php

namespace Buzz\Message;

class FormRequestBuilder
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $files;

    /**
     *
     * @param array $data
     * @param array $files
     */
    public function __construct(array $data = [], array $files = [])
    {
        $this->data = $data;
        $this->files = $files;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function addField($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @param string $name
     * @param string $path
     * @param string|null $contentType
     * @param string|null $filename
     */
    public function addFile($name, $path, $contentType = null, $filename = null)
    {
        $this->files[$name] = [
            'path' => $path,
            'contentType' => $contentType,
            'filename' => $filename,
        ];
    }

    /**
     * @return array
     */
    public function build()
    {
        $data = $this->data;

        foreach ($this->files as $name => $file) {
            $data[$name] = $file;
        }

        return $data;
    }

    /**
     * Reset the builder
     */
    public function reset()
    {
        $this->data = [];
        $this->files = [];
    }
}
