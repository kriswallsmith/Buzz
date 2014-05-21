<?php

namespace Buzz\Message\Form;

use Buzz\Message\AbstractMessage;

class FormUpload extends AbstractMessage implements FormUploadInterface
{
    private $name;
    private $filename;
    private $contentType;
    private $file;

    /**
     * @param null $file        A file
     * @param null $contentType A Content-Type header
     */
    public function __construct($file = null, $contentType = null)
    {
        if ($file) {
            $this->loadContent($file);
        }

        $this->contentType = $contentType;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
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
    public function getFilename()
    {
        if ($this->filename) {
            return $this->filename;
        } elseif ($this->file) {
            return basename($this->file);
        }
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType ?: $this->detectContentType() ?: 'application/octet-stream';
    }

    /**
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * Prepends Content-Disposition and Content-Type headers.
     */
    public function getHeaders()
    {
        $headers = array('Content-Disposition: form-data');

        if ($name = $this->getName()) {
            $headers[0] .= sprintf('; name="%s"', $name);
        }

        if ($filename = $this->getFilename()) {
            $headers[0] .= sprintf('; filename="%s"', $filename);
        }

        if ($contentType = $this->getContentType()) {
            $headers[] = 'Content-Type: '.$contentType;
        }

        return array_merge($headers, parent::getHeaders());
    }

    /**
     * Loads the content from a file.
     */
    public function loadContent($file)
    {
        $this->file = $file;

        parent::setContent(null);
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        parent::setContent($content);

        $this->file = null;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->file ? file_get_contents($this->file) : parent::getContent();
    }

    // private

    private function detectContentType()
    {
        if (!class_exists('finfo', false)) {
            return false;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        return $this->file ? $finfo->file($this->file) : $finfo->buffer(parent::getContent());
    }
}
