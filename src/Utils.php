<?php

declare(strict_types=1);

namespace Lequipe\MockServer;

use function bin2hex;
use function parse_str;
use function preg_replace_callback;

class Utils
{
    /**
     * Custom parse_str() function that does not transform "user.id" to "user_id".
     *
     * @see https://stackoverflow.com/questions/22539633/parse-string-containing-dots-in-php
     * @see https://php.net/manual/en/function.parse-str.php Base parse_str() function
     */
    public static function parse_str_custom(string $string, &$result): void
    {
        $string = preg_replace_callback('/(?:^|(?<=&))[^=[]+/', function ($match) {
            return bin2hex(urldecode($match[0]));
        }, $string);

        parse_str($string, $values);

        $result = array_combine(array_map('hex2bin', array_keys($values)), $values);
    }
}
