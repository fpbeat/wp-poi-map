<?php

namespace WpPoiMap\Repositories;

use WpPoiMap\Utils\Arr;

class PostRepository extends AbstractRepository
{
    /**
     * @var array
     */
    private $objects;

    /**
     * PostRepository constructor.
     * @param array $objects
     */
    public function __construct(array $objects = [])
    {
        parent::__construct();

        $this->objects = $objects;
    }

    /**
     * @return array
     */
    private function getQueryParams(): array
    {
        $data = ['terms' => [], 'taxonomies' => [], 'types' => []];

        foreach ($this->objects as $type => $item) {
            $data['types'][] = $type;
            $data['taxonomies'][] = $item['category'];
            $data['terms'] = array_merge($data['terms'], $item['taxonomies']);
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function get(): array
    {
        $params = $this->getQueryParams();

        if (count($params['types']) > 0 && count($params['taxonomies']) > 0 && count($params['terms']) > 0) {
            $query = "SELECT `p`.*, `r`.`term_taxonomy_id` FROM `{$this->wpdb->prefix}posts` AS `p` LEFT JOIN `{$this->wpdb->prefix}term_relationships` AS `r` ON (`p`.`ID` = `r`.`object_id`) LEFT JOIN `{$this->wpdb->prefix}term_taxonomy` AS `t` ON (`r`.`term_taxonomy_id` = `t`.`term_taxonomy_id`) WHERE `r`.`term_taxonomy_id` IN (" . Arr::queryEscape($params['terms']) . ") AND `p`.`post_type` IN (" . Arr::queryEscape($params['types']) . ") AND `t`.`taxonomy` IN (" . Arr::queryEscape($params['taxonomies']) . ") AND `p`.`post_status` = 'publish' GROUP BY `p`.`ID`";

            return $this->getWpPost($query);
        }

        return [];
    }
}