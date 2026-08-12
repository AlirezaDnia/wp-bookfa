<?php

/**
 * Plugin Name:       WP Bookfa
 * Plugin URI:        https://github.com/your-repo/wp-bookfa
 * Description:       افزونه حرفه‌ای و مدرن رزرو وقت آنلاین مدرسین و مشاورین
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            Alireza Davoodinia
 * Text Domain:       wp-bookfa
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('BOOKFA_VERSION', '1.0.0');
define('BOOKFA_PATH', plugin_dir_path(__FILE__));
define('BOOKFA_URL', plugin_dir_url(__FILE__));

// Autoloader
require_once BOOKFA_PATH . 'includes/Autoloader.php';
\Bookfa\Autoloader::register();

/**
 * کلاس اصلی افزونه (Singleton)
 */
final class WP_Bookfa
{
    private static $instance = null;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->init_hooks();
    }

    private function init_hooks()
    {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        add_action('plugins_loaded', [$this, 'init_plugin']);
    }

    public function activate()
    {
        \Bookfa\Database\Installer::create_tables();
        flush_rewrite_rules();
    }

    public function deactivate()
    {
        flush_rewrite_rules();
    }

    public function init_plugin()
    {
        // Initialize Admin & REST API
        if (is_admin()) {
            new \Bookfa\Admin\AdminMenu();
        }
        new \Bookfa\API\BookingController();
        new \Bookfa\API\AvailabilityController();
        new \Bookfa\Frontend\Shortcode();
    }
}

function wp_bookfa()
{
    return WP_Bookfa::instance();
}

wp_bookfa();
