<?php

namespace WpPoiMap\Utils;

use WpPoiMap\Registry;

class Options {
    /**
     * @return array
     */
    public static function all(): array {
        $options = [];

        foreach (wp_load_alloptions() as $name => $value) {
            if (strpos($name, sprintf('%s_', Registry::instance()['token'])) === 0) {
                $options[preg_replace('/^' . sprintf('%s_', Registry::instance()['token']) . '/', '', $name)] = maybe_unserialize($value);
            }
        }

        return $options;
    }

    /**
     * @param string $name
     * @param string|null $default
     * @return mixed
     */
    public static function get(string $name, ?string $default = NULL) {
        return Arr::get(self::all(), $name, $default);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public static function update(string $name, $value): bool {
        return update_option(sprintf('%s_%s', Registry::instance()['token'], $name), $value);
    }

    /**
     * @param string $option
     * @return bool
     */
    public static function delete(string $option): bool {
        return delete_option(sprintf('%s_%s', Registry::instance()['token'], $option));
    }
}