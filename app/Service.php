<?php

namespace WpPoiMap;

use WpPoiMap\Tools\{Assets, Fenom};
use WpPoiMap\Utils\Options;

class Service
{
    /**
     * @var Service|null
     */
    private static $instance = NULL;

    /**
     * @var Admin\Settings
     */
    private $settings;

    /**
     * @var Output
     */
    private $output;

    /**
     * @var string[]
     */
    private $dependencies = ['polylang/polylang.php', 'advanced-custom-fields/acf.php'];

    /**
     * Service constructor.
     * @param string $file
     */
    public function __construct(string $file = '')
    {
        date_default_timezone_set('Europe/Moscow');

        // Load plugin environment variables
        Registry::instance()->add([
            'version' => Options::get('version'),
            'name' => 'WP POI Map',
            'token' => 'wp-poi-map',
            'file' => $file,
            'dir' => dirname($file),
            'shortcode' => 'wp-poi-map',
            'assets_dir' => trailingslashit(dirname($file)) . 'assets',
            'views_dir' => trailingslashit(dirname($file)) . 'views',
            'cache_dir' => trailingslashit(dirname($file)) . 'cache',
            'assets_url' => esc_url(trailingslashit(plugins_url('/static/', $file))),
            'base_url' => esc_url(trailingslashit(plugins_url('/', $file)))
        ]);

        Registry::instance()->add('fenom', Fenom::instance([
            'force_compile' => WP_POI_MAP_DEBUG,
            'force_verify' => WP_POI_MAP_DEBUG
        ]));

        add_action('init', [$this, 'init'], 11);

        register_activation_hook(Registry::instance()['file'], [$this, 'install']);
    }

    public function init()
    {
        try {
            $this->output = Output::instance($this->settings);

            $this->validateDependencies();

            $this->registerAssets();
            $this->registerShortCode();
            $this->registerAjaxHandler();
        } catch (\Exception $e) {
            add_action('admin_notices', function () use ($e) {
                Registry::instance()['fenom']->display('admin/components/error.tpl', ['message' => $e->getMessage()]);
            });
        }
    }

    /**
     * @param $file
     * @return Service
     */
    public static function instance($file): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($file);
        }

        return self::$instance;
    }

    /**
     * @param Admin\Settings $settings
     */
    public function setSettingsInstance(Admin\Settings $settings): void
    {
        $this->settings = $settings;
    }

    /**
     * @param $file
     * @param float $default
     * @return float
     */
    public function getVersion($file, $default = 1.0): float
    {
        if (function_exists('get_plugin_data')) {
            $version = get_plugin_data($file)['Version'] ?: $default;

            return floatval($version);
        }

        return floatval($default);
    }

    /**
     * @return void
     */
    public function install(): void
    {
        Options::update('version', $this->getVersion(Registry::instance()['file']));
    }

    /**
     * @return void
     */
    public function uninstall(): void
    {
        foreach (['version'] as $option) {
            Options::delete($option);
        }
    }

    /**
     * @return void
     */
    private function registerAssets(): void
    {
        Assets::register(Assets::ENQUEUE_ADMIN,
            '/css/admin.bundle.css'
        );

        Assets::register(Assets::ENQUEUE_WEB,
            '/css/web.bundle.css',
            '/js/web.bundle.js'
        );
    }

    /**
     * @return void
     */
    private function registerShortCode(): void
    {
        add_shortcode(Registry::instance()['shortcode'], [$this->output, 'render']);
    }

    /**
     * @throws \Exception
     * @return void
     */
    private function validateDependencies(): void
    {
        foreach ($this->dependencies as $dependency) {
            if (!in_array($dependency, apply_filters('active_plugins', get_option('active_plugins')))) {
                throw new \Exception(sprintf('Плагін %s, потребує допоміжний плагін %s', Registry::instance()['name'], $dependency));
            }
        }
    }

    /**
     * @return void
     */
    private function registerAjaxHandler(): void
    {
        add_action(sprintf('wp_ajax_%s_data', Registry::instance()['token']), [$this->output, 'data']);
        add_action(sprintf('wp_ajax_nopriv_%s_data', Registry::instance()['token']), [$this->output, 'data']);
    }
}