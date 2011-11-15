<?php

namespace Buzz\Message;

interface MessageInterface
{
    function getHeaders();
    function addHeader($header);
    function getContent();
}
