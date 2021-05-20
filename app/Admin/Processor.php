<?php

namespace WpPoiMap\Admin;

use WpPoiMap\Registry;

class Processor {

    /**
     * @var null
     */
    private static $instance = NULL;

    /**
     * @var string
     */
    private $metaFieldKey;

    /**
     * @return Processor
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Processor constructor.
     */
    public function __construct() {
        $this->metaFieldKey = sprintf('%s_data', Registry::instance()['token']);
    }

    /**
     * @param int $postID
     * @param \WP_Post $post
     */
    public function save($postID, \WP_Post $post) {
        if ($post->post_status === 'publish' && has_shortcode($post->post_content, Registry::instance()['shortcode'])) {
            $data = $this->getPostShortcodes($post);
            update_post_meta($postID, $this->metaFieldKey, $this->process($data));
        } else {
            delete_post_meta($postID, $this->metaFieldKey);
        }
    }

    /**
     * @param \WP_Post $post
     * @return array
     */
    public function getPostShortcodes(\WP_Post $post) {
        $pattern = get_shortcode_regex([Registry::instance()['shortcode']]);
        preg_match_all("/$pattern/", $post->post_content, $matches, PREG_SET_ORDER);

        $pool = [];
        foreach ($matches as $shortcode) {
            $pool = array_merge($pool, $this->getTagAttributes($shortcode));
        }

        $urlColumn = array_unique(array_column($pool, 'url'));

        return array_intersect_key($pool, $urlColumn);
    }

    /**
     * @param array $shortcode
     * @return array
     */
    public function getTagAttributes(array $shortcode) {
        $params = shortcode_parse_atts($shortcode[3]);

        $pool = [];
        foreach ((array)$params as $name => $value) {
            if (in_array($name, Transport::SUPPORTED) && wp_http_validate_url($value) !== FALSE) {
                array_push($pool, [
                    'type' => strtolower($name),
                    'url' => $value
                ]);

                break;
            }
        }

        return $pool;
    }

    /**
     * @param array $sections
     * @return array
     */
    private function process(array $sections) {
        $pool = [];
        foreach ($sections as $section) {
            try {
                $transport = Transport::factory($section, $this->downloadFile($section['url']));
                $data = $transport->parse();

                $pool[md5($transport->getUrl())] = $data->toArray();
            } catch (\Exception $e) {
                // nope
            }
        }

        return $pool;
    }

    /**
     * @param string $url
     * @return mixed
     * @throws \Exception
     */
    private function downloadFile($url) {
        $response = wp_remote_request($url, [
            'timeout' => 10
        ]);

        if (in_array(wp_remote_retrieve_response_code($response), [200, 301, 302])) {
            if (is_wp_error($response) || !isset($response['body'])) {
                throw new \Exception;
            }

            return $response['body'];

        }

        throw new \Exception;
    }
}