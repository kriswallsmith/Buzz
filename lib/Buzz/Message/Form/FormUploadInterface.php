<?php

namespace Buzz\Message\Form;

use Buzz\Message\MessageInterface;

interface FormUploadInterface extends MessageInterface
{
    function setName($name);
    function getFile();
    function getFilename();
    function getContentType();
}
