<?php

namespace Buzz\Message\Form;

use Buzz\Message\MessageInterface;

interface FormUploadInterface extends MessageInterface
{
    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getFile();

    /**
     * @return string
     */
    public function getFilename();

    /**
     * @return string
     */
    public function getContentType();
}
