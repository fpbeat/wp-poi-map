<?php

namespace WpPoiMap;

use WpPoiMap\Admin\Settings;
use WpPoiMap\Tools\Fenom;
use WpPoiMap\Utils\Arr;

class Output
{
    /**
     * @var Output|null
     */
    private static $instance = NULL;

    /**
     * @var Fenom
     */
    private $fenom;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var TemplateFormatter
     */
    private $template;
    /**
     * @var DataService
     */
    private $dataService;

    /**
     * @var Translation
     */
    private $translation;

    /**
     * Output constructor.
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->fenom = Registry::instance()['fenom'];
        $this->settings = $settings;

        $this->dataService = new DataService($this->settings->getOptions('object:*'), $this->settings->getOptions('icon:*'));

        $this->template = new TemplateFormatter($this->settings->getOptions('map-template'));

        $translation = new Translation;
        $translation->register();
    }

    /**
     * @param Settings $settings
     * @return self
     */
    public static function instance(Settings $settings): self
    {
        if (self::$instance === NULL) {
            self::$instance = new self($settings);
        }

        return self::$instance;
    }

    /**
     * @param array $object
     * @return string
     */
    private function formatTemplate(array $object)
    {
        return $this->template->render(Arr::extract($object, ['name', 'address', 'phone', 'email', 'website', 'image', 'permalink']));
    }

    /**
     * @return void
     */
    public function data()
    {
        if (wp_verify_nonce($_POST['nonce'], Registry::instance()['token'])) {

            $objects = array_map(function ($object) {
                $object['template'] = $this->formatTemplate($object);

                return Arr::extract($object, ['name', 'type', 'geo', 'template', 'icon']);
            }, $this->dataService->toArray());

            wp_send_json($objects);
        }

        wp_die(NULL, NULL, 500);
    }

    /**
     * @param array $attributes
     * @return string
     */
    public function render($attributes = [])
    {
        $allAttributes = shortcode_atts($this->settings->getOptions(['map-api-key', 'map-center', 'map-zoom', 'map-type', 'map-behavior']), $attributes);

        $post = get_post();
        $types = $this->dataService->getTypes();

        return $this->fenom->fetch('web/output.tpl', [
            'token' => Registry::instance()['token'],
            'types' => $types,
            'selected' => array_key_exists($post->post_type, $this->dataService->getTypes()) ? [$post->post_type] : array_keys($types),
            'pageID' => $post->ID,
            'language' => pll_current_language(),
            'settings' => Arr::camelCase($allAttributes),
            'iconsPath' => Registry::instance()['assets_url']
        ]);

    }
}