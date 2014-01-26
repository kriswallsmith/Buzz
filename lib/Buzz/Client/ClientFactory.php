<?php

namespace Buzz\Client;


use Buzz\Exception\RuntimeException;

/**
 * Class ClientFactory
 * @package Buzz\Client
 * Automatically create needing ClientInterface class, depending of existence function
 */
class ClientFactory {

    /**
     * @return ClientInterface
     * @throws \Buzz\Exception\RuntimeException
     */
    public function create() {
        if(function_exists("curl_exec")) {
            return new Curl();
        } else if(function_exists("file_get_contents")) {
            return new FileGetContents();
        } else {
            throw new RuntimeException("Couldn't find right driver");
        }
    }
} 