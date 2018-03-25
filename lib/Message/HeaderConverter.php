<?php

declare(strict_types=1);

namespace Buzz\Message;

/**
 * Convert between Buzz style:
 * array(
 *   'foo: bar',
 *   'baz: biz',
 * ).
 *
 * and PSR style:
 * array(
 *   'foo' => 'bar'
 *   'baz' => ['biz', 'buz'],
 * )
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class HeaderConverter
{
    /**
     * Convert from Buzz style headers to PSR style.
     *
     * @param array $headers
     *
     * @return array
     */
    public static function toBuzzHeaders(array $headers): array
    {
        $buzz = [];

        foreach ($headers as $key => $values) {
            if (!is_array($values)) {
                $buzz[] = sprintf('%s: %s', $key, $values);
            } else {
                foreach ($values as $value) {
                    $buzz[] = sprintf('%s: %s', $key, $value);
                }
            }
        }

        return $buzz;
    }

    /**
     * Convert from PSR style headers to Buzz style.
     *
     * @param array $headers
     *
     * @return array
     */
    public static function toPsrHeaders(array $headers): array
    {
        $psr = [];
        foreach ($headers as $header) {
            list($key, $value) = explode(':', $header, 2);
            $psr[trim($key)][] = trim($value);
        }

        return $psr;
    }
}
