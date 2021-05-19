<?php

namespace WpPoiMap;

use WpPoiMap\Layers\{IconLayer, PostLayer, ImageLayer};
use WpPoiMap\Repositories\PostRepository;
use WpPoiMap\Tools\ObjectPost;
use WpPoiMap\Utils\Arr;

class DataService
{
    /**
     * @var array
     */
    private $objects;

    /**
     * @var array
     */
    private $icons;

    /**
     * @var ObjectPost
     */
    private $objectPost;

    /**
     * @var PostRepository
     */
    private $postRepository;

    /**
     * @var PostLayer
     */
    private $postLayer;

    /**
     * @var IconLayer
     */
    private $iconLayer;

    /**
     * @var ImageLayer
     */
    private $imageLayer;

    /**
     * DataService constructor.
     * @param array $objects
     * @param array $icons
     */
    public function __construct(array $objects, array $icons)
    {
        $this->objectPost = new ObjectPost();
        $this->objects = $this->parseObjects($objects);
        $this->icons = $this->parseIcons($icons);

        $this->postRepository = new PostRepository($this->objects);

        $this->postLayer = new PostLayer();
        $this->imageLayer = new ImageLayer();
        $this->iconLayer = new IconLayer($this->objects, $this->icons);

    }

    /**
     * @param array $objects
     * @return array
     */
    private function parseObjects(array $objects): array
    {
        $output = [];
        foreach ($objects as $key => $taxonomies) {
            $key = preg_replace('/^object\-/i', '', $key);

            try {
                if (count($taxonomies) === 0) {
                    throw new \Exception;
                }

                $output[$key] = [
                    'category' => $this->objectPost->getPostCategoryTaxonomy($key),
                    'taxonomies' => array_map('pll_get_term', $taxonomies)
                ];
            } catch (\Exception $e) {
                // none
            }
        }

        return $output;
    }

    /**
     * @return array
     */
    public function getTypes(): array
    {
        $types = array_keys($this->objects);

        return array_filter($this->objectPost->getPostTypes(), function ($type) use ($types) {
            return in_array($type, $types);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param array $icons
     * @return array
     */
    private function parseIcons(array $icons): array
    {
        return Arr::arrayBothMap(function ($key, $value) {
            $key = preg_replace('/^icon\-/i', '', $key);

            return [intval($key), $value];
        }, $icons);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $posts = $this->postRepository->get();

        if (count($posts) > 0) {
            $objects = array_replace_recursive($this->postLayer->toArray($posts), $this->iconLayer->toArray($posts), $this->imageLayer->toArray($posts));

            return array_filter($objects, function ($object) {
                return isset($object['geo']) && $object['geo'] !== NULL;
            });
        }

        return [];
    }
}