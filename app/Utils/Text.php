<?php

namespace WpPoiMap\Utils;

use Fresh\Transliteration\Transliterator;

class Text
{
    /**
     * @param string $string
     * @return string
     */
    public static function camelCase(string $string): string
    {
        return preg_replace_callback('/-\D/i', function ($match) {
            return strtoupper(substr($match[0], 1, 1));
        }, $string);
    }

    /**
     * @param string $string
     * @param string $glue
     * @return string
     */
    public static function transliterator(string $string, string $glue = '-'): string
    {
        $transliterator = new Transliterator;

        $string = $transliterator->ruToEn($string);
        $string = $transliterator->ukToEn($string);

        $string = preg_replace(['/[^[:alnum:]]/', '/' . preg_quote($glue) . '{1,}/'], $glue, filter_var($string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));

        return trim($string, $glue);
    }
}