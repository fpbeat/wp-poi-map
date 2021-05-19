<?php

namespace WpPoiMap\Repositories;

use WpPoiMap\Layers\PostLayer;
use WpPoiMap\Utils\Arr;

class AcfRepository extends AbstractRepository
{
    /**
     * @var array
     */
    private $ids;

    /**
     * AcfRepository constructor.
     * @param array $ids
     */
    public function __construct(array $ids = [])
    {
        parent::__construct();

        $this->ids = $ids;
    }

    /**
     * @return string
     */
    private function getMetaMask(): string
    {
        return sprintf('^(%s)', implode('|', PostLayer::CONTENT_REGEXPS));
    }

    /**
     * @inheritDoc
     */
    public function get(): array
    {
        $query = "SELECT `p`.* FROM `{$this->wpdb->prefix}postmeta` AS `p` WHERE `p`.`post_id` IN (" . Arr::queryEscape($this->ids) . ") AND `p`.`meta_key` REGEXP '" . $this->getMetaMask() . "'";

        return $this->wpdb->get_results($query) ?? [];
    }
}