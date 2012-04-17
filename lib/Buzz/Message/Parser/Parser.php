<?php

namespace Buzz\Message\Parser;

use Buzz\Message\MessageInterface;

class Parser
{
    /**
     * Parses a raw HTTP message into an object.
     *
     * @param string $raw A raw HTTP
     */
    public function parse($raw, MessageInterface $message)
    {
        $lines = preg_split('/(\\r?\\n)/', $raw, -1, PREG_SPLIT_DELIM_CAPTURE);
        for ($i = 0, $count = count($lines); $i < $count; $i += 2) {
            $line = $lines[$i];

            if (empty($line)) {
                $message->setContent(implode('', array_slice($lines, $i + 2)));
                return;
            }

            $message->addHeader($line);
        }
    }
}
