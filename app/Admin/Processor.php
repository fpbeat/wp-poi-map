<?php

namespace WpPoiMap\Admin;

use WpPoiMap\Registry;

class Processor {

    private static $instance = NULL;
    private $metaFieldKey;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function __construct() {
        $this->metaFieldKey = sprintf('%s_data', Registry::instance()['token']);
    }

    public function save($postID, $post) {
        if ($post->post_status === 'publish' && has_shortcode($post->post_content, Registry::instance()['shortcode'])) {
            $data = $this->getPostShortcodes($post);
            update_post_meta($postID, $this->metaFieldKey, $this->process($data));
        } else {
            delete_post_meta($postID, $this->metaFieldKey);
        }
    }

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

    public function getTagAttributes($shortcode) {
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

    private function process($sections) {
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