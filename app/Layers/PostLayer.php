<?php

namespace WpPoiMap\Layers;

use WpPoiMap\Registry;
use WpPoiMap\Repositories\AcfRepository;
use WpPoiMap\Utils\Arr;

class PostLayer
{
    /**
     * @var array
     */
    public const CONTENT_REGEXPS = [
        'geo' => 'info_tabs_[0-9]_tab_content',
        'address' => 'address_[0-9]_address_text',
        'phone' => 'phone_[0-9]_phone_number',
        'email' => 'e-mail_[0-9]_e-mail_address',
        'website' => 'website'
    ];

    /**
     * @var array
     */
    private $posts;

    /**
     * @var array
     */
    private $ids;

    /**
     * @var array
     */
    private $pool;

    /**
     * @var string[]
     */
    private $fields = ['name', 'permalink', 'type', 'geo', 'address', 'phone', 'email', 'website'];

    /**
     * @var AcfRepository
     */
    private $acfRepository;

    /**
     * @var array
     */
    private $language;

    /**
     * PostLayer constructor.
     */
    public function __construct()
    {
        $this->language = [
            'default' => pll_default_language(),
            'current' => pll_current_language()
        ];
    }

    /**
     * @param array $posts
     */
    public function bootstrap(array $posts): void
    {
        $this->posts = $posts;

        $this->ids = array_map(function ($value) {
            return $value->ID;
        }, $posts);

        $this->acfRepository = new AcfRepository($this->ids);

        $this->pool = $this->initEmptyPool();
    }

    /**
     * @param array $posts
     * @return array
     */
    public function toArray(array $posts): array
    {
        $this->bootstrap($posts);
        $this->getAcfValues();

        foreach ($this->posts as $post) {
            array_map(function ($name) use ($post) {
                $this->getRawValue($post->ID, $name, $post);
            }, ['name', 'permalink', 'type']);
        }

        $this->normalize();

        return $this->pool;
    }

    /**
     * @param int $id
     * @param string $name
     * @param string|\WP_Post $content
     */
    private function getRawValue(int $id, string $name, $content): void
    {
        $output = call_user_func([$this, sprintf('get%s', ucfirst($name))], $content);

        if (!is_null($output)) {
            $this->pool[$id][$name][] = $output;
        }
    }

    /**
     * @return void
     */
    public function getAcfValues(): void
    {
        foreach ($this->acfRepository->get() as $result) {
            foreach (self::CONTENT_REGEXPS as $name => $regexp) {
                if (preg_match(sprintf('/^%s$/i', $regexp), $result->meta_key)) {
                    $this->getRawValue($result->post_id, $name, $result->meta_value);
                }
            }
        }
    }

    /**
     * @return array
     */
    private function initEmptyPool(): array
    {
        return Arr::arrayBothMap(function ($key, $value) {
            return [$value, array_fill_keys($this->fields, [])];
        }, $this->ids);
    }

    /**
     * @param string $content
     * @return array|null
     */
    public function getGeo(string $content): ?array
    {
        if (preg_match('/\[' . Registry::instance()['shortcode'] . '\s([^\]]*)\]/', $content, $match)) {
            $attributes = shortcode_parse_atts($match[1]);

            if (isset($attributes['lat']) && isset($attributes['lng'])) {
                return $attributes;
            }
        }

        return NULL;
    }

    /**
     * @param \WP_Post $post
     * @return string
     */
    public function getName(\WP_Post $post): string
    {
        return get_the_title($post);
    }

    /**
     * @param string $content
     * @return string|null
     */
    public function getAddress(string $content): ?string
    {
        return $content ?: NULL;
    }

    /**
     * @param string $content
     * @return string|null
     */
    public function getPhone(string $content): ?string
    {
        return $content ?: NULL;
    }

    /**
     * @param string $content
     * @return string|null
     */
    public function getEmail(string $content): ?string
    {
        return filter_var($content, FILTER_VALIDATE_EMAIL) ? $content : NULL;
    }

    /**
     * @param string $content
     * @return string|null
     */
    public function getWebsite(string $content): ?string
    {
        if (!preg_match('/^https?:\/\//i', $content)) {
            $content = sprintf('http://', $content);
        }

        return filter_var($content, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED) ? $content : NULL;
    }

    /**
     * @param \WP_Post $post
     * @return string|null
     */
    public function getType(\WP_Post $post): ?string
    {
        return $post->post_type ?: NULL;
    }

    /**
     * @param \WP_Post $post
     * @return string
     */
    public function getPermalink(\WP_Post $post): string
    {
        $link = ($this->language['current'] !== $this->language['default']) ? [$this->language['current']] : [];
        array_push($link, $this->getType($post), get_post_field('post_name', $post));

        return site_url(implode('/', $link));
    }

    /**
     * @return void
     */
    private function normalize(): void
    {
        foreach ($this->pool as $key => $record) {
            $this->pool[$key] = array_map(function ($value) {
                return count($value) > 0 ? reset($value) : NULL;
            }, $record);
        }
    }
}