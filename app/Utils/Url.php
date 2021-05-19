<?php

namespace WpPoiMap\Utils;

use WpPoiMap\Registry;

class Url
{
    /**
     * @param string $path
     * @return string
     */
    public static function getAsset(string $path): string
    {
        $path = trim($path, '/');
        $url = sprintf('%s%s', Registry::instance()['assets_url'], $path);

        return esc_url($url);
    }
}