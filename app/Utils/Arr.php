<?php

namespace WpPoiMap\Utils;

class Arr
{
    /**
     * @param array $input
     * @return string
     */
    public static function getHash(array $input): string
    {
        array_multisort($input);

        return md5(json_encode($input));
    }

    /**
     * @param callable $fnc
     * @param array $array
     * @return array
     */
    public static function arrayBothMap(callable $fnc, array $array)
    {
        return array_column(array_map($fnc, array_keys($array), $array), 1, 0);
    }

    /**
     * @param array $array
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public static function get(array $array, string $key, ?string $default = NULL): ?string
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }

    /**
     * @param $array
     * @param array $keys
     * @param string|null $default
     * @return array
     */
    public static function extract($array, array $keys, ?string $default = NULL): array
    {
        return array_filter($array, function ($item) use ($keys) {
                return in_array($item, $keys);
            }, ARRAY_FILTER_USE_KEY) + array_fill_keys($keys, $default);
    }

    /**
     * @param array $array
     * @return array
     */
    public static function camelCase(array $array): array
    {
        return self::arrayBothMap(function ($key, $value) {
            return [Text::camelCase($key), $value];
        }, $array);
    }

    /**
     * @param array $array
     * @return string
     */
    public static function queryEscape(array $array): string
    {
        global $wpdb;

        $escaped = array_map(function ($value) use ($wpdb) {
            return $wpdb->prepare(is_numeric($value) ? '%d' : '%s', $value);
        }, $array);

        return implode(',', $escaped);
    }

    /**
     * @param array $input
     * @return array
     */
    public static function expand(array $input): array
    {
        $keys = array_keys($input);
        $key = reset($keys);

        return [$key, $input[$key]];
    }

    /**
     * @param array $array
     * @param string $column
     * @return array
     */
    public static function column(array $array, string $column): array
    {
        return array_map(function ($value) use ($column) {
            return is_object($value) ? $value->{$column} : $value[$column];
        }, $array);
    }
}