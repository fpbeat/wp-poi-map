<?php
/*
 * Plugin Name: Карта об'єктів
 * Version: 1.0
 * Description: Карта об'єктів з групуванням по категоріях
 * Author: Roman Zhakhov
 * Author URI: https://fpbeat.name
 * Requires at least: 5.0
 * Tested up to: 5.4
 *
 * Text Domain: wp-poi-map
 *
 * @package WordPress
 * @author Roman Zhakhov
 * @since 1.0.0
 */

require_once 'vendor/autoload.php';

define('WP_POI_MAP_DEBUG', TRUE);

$instance = WpPoiMap\Service::instance(__FILE__);
$instance->setSettingsInstance(WpPoiMap\Admin\Settings::instance());

