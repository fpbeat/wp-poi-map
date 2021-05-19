<?php

namespace WpPoiMap\Layers;

class IconLayer
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
     * IconLayer constructor.
     * @param array $objects
     * @param array $icons
     */
    public function __construct(array $objects, array $icons)
    {
        $this->objects = $objects;
        $this->icons = $icons;
    }

    /**
     * @param $post
     * @return string|null
     */
    public function getIcon($post): ?string
    {
        $taxonomy = ($this->objects[$post->post_type]['category']);

        $termId = icl_object_id($post->term_taxonomy_id, $taxonomy, false, pll_default_language());

        return $this->icons[$termId] ?: NULL;
    }

    /**
     * @param array $posts
     * @return array
     */
    public function toArray(array $posts): array
    {
        $pool = [];
        foreach ($posts as $post) {
            $pool[$post->ID]['icon'] = $this->getIcon($post);
        }

        return $pool;
    }
}