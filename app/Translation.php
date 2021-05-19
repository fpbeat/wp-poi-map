<?php

namespace WpPoiMap;

use WpPoiMap\Traits\TranslationCacheable;
use WpPoiMap\Utils\Text;

class Translation
{
    use TranslationCacheable;

    /**
     * @var string
     */
    const TRANSLATION_REGEXP = '/{t[^}]*}(.+?){\/t}/si';

    /**
     * @param string|null $content
     * @return array
     */
    public function parseStrings(?string $content): array
    {
        preg_match_all(self::TRANSLATION_REGEXP, $content, $matches, PREG_SET_ORDER);

        return array_map(function ($match) {
            return trim($match[1]);
        }, $matches);
    }

    /**
     * @return array
     */
    private function discover(): array
    {
        $all = [];
        foreach ($this->getTranslations() as $translation) {
            $all = array_merge($all, $translation);
        }

        if (!WP_POI_MAP_DEBUG) {
            $this->storeCache($all);
        }

        return $all;
    }

    /**
     * @return \Generator
     */
    private function getTranslations(): \Generator
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(Registry::instance()['views_dir'], \RecursiveDirectoryIterator::SKIP_DOTS));

        foreach ($iterator as $path) {
            $content = file_get_contents($path->getPathname());

            if ($content !== FALSE) {
                yield $this->parseStrings($content);
            }
        }
    }

    /**
     * @param mixed ...$texts
     */
    public function add(...$texts)
    {
        foreach (func_get_args() as $text) {
            pll_register_string(Text::transliterator($text), $text, Registry::instance()['name'], strpos($text, "\n") !== FALSE);
        }
    }

    public function register()
    {
        $translations = (WP_POI_MAP_DEBUG ? $this->discover() : ($this->readCache() ?? $this->discover()));

        $this->add(...$translations);
    }
}