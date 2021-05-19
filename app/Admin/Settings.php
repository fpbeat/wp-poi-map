<?php

namespace WpPoiMap\Admin;

use WpPoiMap\Admin\Settings\{AbstractSettings, IconSettings, ObjectSettings, MapSettings};
use WpPoiMap\Registry;
use WpPoiMap\Tools\Fenom;

class Settings
{

    /**
     * @var Settings
     */
    private static $instance = NULL;

    /**
     * @var array
     */
    private $pool = [];

    /**
     * @var \TitanFramework
     */
    private $titan;

    public function __construct()
    {
        $this->titan = \TitanFramework::getInstance(Registry::instance()['token']);

        add_action('plugins_loaded', [$this, 'loadTitanLocale']);
        add_action('tf_create_options', [$this, 'registerOptions']);

        add_action('tf_save_admin_' . $this->titan->optionNamespace, [$this, 'afterSaveAction'], 10, 2);
    }

    /**
     * @return static
     */
    public static function instance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @param AbstractSettings $setting
     */
    private function register(AbstractSettings $setting): void
    {
        $this->pool[] = $setting;

        add_action('init', [$setting, 'create'], 10);
    }

    /**
     * @return \TitanFrameworkAdminPage
     */
    private function buildSettingPage(): \TitanFrameworkAdminPage
    {
        return $this->titan->createContainer([
            'parent' => 'options-general.php',
            'name' => 'Карта об\'єктів',
            'icon' => 'dashicons-location',
            'id' => Registry::instance()['token'],
            'type' => 'admin-page'
        ]);
    }

    /**
     * @return void
     */
    public function registerOptions(): void
    {
        $panel = $this->buildSettingPage();

        $this->register(new MapSettings($panel));
        $this->register(new ObjectSettings($panel));
        $this->register(new IconSettings($panel));
    }

    /**
     * @param string|array $option
     * @return array|mixed
     */
    public function getOptions($option)
    {
        if (is_array($option)) {
            return $this->titan->getOptions(array_flip($option));
        }

        if (substr($option, -2) === ':*') {
            $needle = substr($option, 0, -2);

            $keys = array_filter(array_keys($this->titan->optionsUsed), function ($value) use ($needle) {
                return strpos($value, $needle) === 0;
            });

            return $this->getOptions($keys);
        }

        return $this->titan->getOption($option);
    }

    /**
     * @param $titan
     * @param \TitanFrameworkAdminTab $tab
     */
    public function afterSaveAction($titan, \TitanFrameworkAdminTab $tab): void
    {
        switch ($tab->settings['id']) {
            case MapSettings::TAB_ID:
                $path = Registry::instance()['views_dir'] . '/shared/template.tpl';

                if (is_writeable($path)) {
                    file_put_contents($path, $this->titan->getOption('map-template'), \LOCK_EX);
                    Registry::instance()['fenom']->clearAllCompiles();
                }
                break;
            default:
        }
    }

    /**
     * @return void
     */
    public function loadTitanLocale(): void
    {
        try {
            $reflector = new \ReflectionClass(\TitanFrameworkPlugin::class);

            $mofile = \TF_I18NDOMAIN . '-' . apply_filters('plugin_locale', determine_locale(), \TF_I18NDOMAIN) . '.mo';
            load_textdomain(\TF_I18NDOMAIN, dirname($reflector->getFileName()) . '/languages/' . $mofile);
        } catch (\ReflectionException $e) {
            // none
        }
    }
}