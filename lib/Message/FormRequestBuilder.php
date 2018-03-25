<?php

declare(strict_types=1);

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
    public function addField(string $name, string $value): void
    {
        $this->data[$name] = $value;
    }

    /**
     * @param string      $name
     * @param string      $path
     * @param string|null $contentType
     * @param string|null $filename
     */
    public function addFile(string $name, string $path, string $contentType = null, string $filename = null): void
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
    public function build(): array
    {
        $data = $this->data;

        foreach ($this->files as $name => $file) {
            $data[$name] = $file;
        }

        return $data;
    }

    /**
     * Reset the builder.
     */
    public function reset(): void
    {
        $this->data = [];
        $this->files = [];
    }
}
