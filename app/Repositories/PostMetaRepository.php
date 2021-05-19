<?php

namespace WpPoiMap\Repositories;

use WpPoiMap\Utils\Arr;

class PostMetaRepository extends AbstractRepository
{
    /**
     * @var string
     */
    private const ATTACHMENT_KEY = '_wp_attachment_metadata';

    /**
     * @var string
     */
    private const THUMB_ID_KEY = '_thumbnail_id';

    /**
     * @var array
     */
    private $ids;

    /**
     * PostRepository constructor.
     * @param array $ids
     */
    public function __construct(array $ids = [])
    {
        parent::__construct();

        $this->ids = $ids;
    }

    /**
     * @inheritDoc
     */
    public function get(): array
    {
        $query = "SELECT `m`.`post_id`, (SELECT `r`.`meta_value` FROM `{$this->wpdb->prefix}postmeta` AS `r` WHERE `r`.`post_id` = `m`.`meta_value` AND `r`.`meta_key` = '" . self::ATTACHMENT_KEY . "') AS `meta_value` FROM `{$this->wpdb->prefix}postmeta` AS `m` WHERE `m`.`post_id` IN (" . Arr::queryEscape($this->ids) . ") AND `m`.`meta_key` = '" . self::THUMB_ID_KEY . "'";

        return $this->getWpPostMeta($query);
    }
}