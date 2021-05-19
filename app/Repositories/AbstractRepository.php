<?php

namespace WpPoiMap\Repositories;

abstract class AbstractRepository
{
    /**
     * @var \wpdb
     */
    protected $wpdb;

    /**
     * AcfRepository constructor.
     */
    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
    }

    /**
     * @param string $query
     * @return array
     */
    protected function getWpPost(string $query): array
    {
        $results = $this->wpdb->get_results($query) ?? [];

        return array_map(function ($value) {
            $post = new \WP_Post($value);

            wp_cache_add($post->ID, $post, 'posts');

            return $post;
        }, $results);
    }

    /**
     * @param string $query
     * @return array
     */
    protected function getWpPostMeta(string $query): array
    {
        $results = $this->wpdb->get_results($query) ?? [];

        return array_map(function ($value) {
            if (is_serialized($value->meta_value)) {
                $value->meta_value = @unserialize($value->meta_value);
            }

            return $value;
        }, $results);
    }

    /**
     * @return array
     */
    abstract public function get(): array;
}