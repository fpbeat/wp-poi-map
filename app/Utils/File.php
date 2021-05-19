<?php

namespace WpPoiMap\Utils;

class File
{
    /**
     * @param string $type
     * @param array $condition
     * @return bool
     */
    public static function fileIs(string $type, array $condition): bool
    {
        list($name, $value) = Arr::expand($condition);

        switch ($name) {
            case 'mime':
                return strpos($value, $type . '/') === 0;

            case 'file':
                $check = wp_check_filetype($value);

                if (empty($check['ext'])) {
                    return FALSE;
                }

                $types = [
                    'image' => ['jpg', 'jpeg', 'jpe', 'gif', 'png'],
                    'audio' => wp_get_audio_extensions(),
                    'video' => wp_get_video_extensions()
                ];

                return in_array($check['ext'], $types[$type] ?? []);
        }

        return FALSE;
    }
}