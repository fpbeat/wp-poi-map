<?php

namespace WpPoiMap\Tools;

use WpPoiMap\Registry;
use WpPoiMap\Utils\Url;

class Assets
{
    const ENQUEUE_ADMIN = 'admin_enqueue_scripts';
    const ENQUEUE_WEB = 'wp_enqueue_scripts';

    const FILE_CSS = 'css';
    const FILE_JS = 'js';

    /**
     * Assets constructor.
     *
     * @param string $type
     * @param array $files
     */
    public function __construct(string $type, array $files)
    {
        add_action($type, function () use ($files) {
            $this->processFiles($files);
        }, 10);
    }

    /**
     * @param mixed ...$params
     */
    public static function register(...$params): void
    {
        $type = array_shift($params);

        new self($type, $params);
    }

    /**
     * @param array $files
     */
    private function processFiles(array $files): void
    {
        foreach ($files as $file) {
            $file = !$this->isAbsolutePath($file) ? Url::getAsset($file) : $file;

            switch($this->getFileType($file)) {
                case self::FILE_CSS:
                    wp_enqueue_style($this->getPathHandle($file) , $file, [], $this->getVersion());
                    break;
                case self::FILE_JS:
                    wp_enqueue_script($this->getPathHandle($file), $file, [], $this->getVersion());
                    break;
            }
        }
    }

    /**
     * @param string $path
     * @return bool
     */
    private function isAbsolutePath(string $path): bool
    {
        return preg_match('/^https?:\/\//i', $path) || substr($path, 0, 2) === '//';
    }

    /**
     * @param string $url
     * @return string|null
     */
    private function getFileType(string $url): ?string {
        $path = parse_url($url, PHP_URL_PATH);

        if (preg_match('/(?:\.|\/)(css|js)$/', $path, $match)) {
            return strtolower($match[1]);
        }

        return NULL;
    }

    /**
     * @param string $url
     * @return string
     */
    private function getPathHandle(string $url): string {
        $hash = substr(md5($url), 0, 12);

        return sprintf('%s_%s',Registry::instance()['token'], $hash);
    }

    /**
     * @return string
     */
    private function getVersion(): string {
        if (WP_POI_MAP_DEBUG) {
            return uniqid();
        }

        return strval(Registry::instance()['version']);
    }
}