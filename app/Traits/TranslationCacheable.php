<?php

namespace WpPoiMap\Traits;

use WpPoiMap\Registry;

trait TranslationCacheable
{
    /**
     * @var string
     */
    private $cacheFileName = 'translations.cache';

    /**
     * @return string
     */
    private function getCachePath(): string
    {
        return sprintf('%s/%s', Registry::instance()['cache_dir'], $this->cacheFileName);
    }

    /**
     * @param array $content
     * @return bool
     */
    protected function storeCache(array $content): bool
    {
        $content = json_encode($content);

        return file_put_contents($this->getCachePath(), $content) !== FALSE;
    }

    /**
     * @return array|null
     */
    protected function readCache(): ?array
    {
        $content = file_get_contents($this->getCachePath());

        $content = json_decode($content, TRUE);
        if ($content !==FALSE && json_last_error() === JSON_ERROR_NONE) {
            return $content;
        }

        return NULL;
    }
}