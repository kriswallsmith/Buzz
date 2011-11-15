<?php

namespace Buzz\Message;

interface RequestInterface extends MessageInterface
{
    function getMethod();
    function getResource();
    function getProtocolVersion();
    function getHost();
    function getUrl();
    function isSecure();
}
