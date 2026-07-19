<?php

/**
 * Plugin Name: wp-bookfa
 * Plugin URI:  https://github.com/AlirezaDnia/wp-bookfa
 * Description: افزونه رزو وقت بر اساس تاریخ شمسی و مدیریت مدرسین/مشاورین 
 * Version:     1.0.0
 * Author:      Alireza Davoodinia
 * Text Domain: wp-bookfa
 * Domain Path: /languages
 * Requires PHP: 8.1
 * Requires at least: 7.0
 */

if (! defined('ABSPATH')) {
    exit;
}

// لود خودکار کلاس‌ها از طریق کامپوزر
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// کدهای فعال‌سازی افزونه (ساخت دیتابیس)
register_activation_hook(__FILE__, ['WPBookfa\WPBF_Activator', 'activate']);

// راه‌اندازی ماژول‌های اصلی
add_action('plugins_loaded', 'wpbf_init_plugin');

function wpbf_init_plugin(): void
{
    if (is_admin()) {
        new WPBookfa\WPBF_Admin();
    }
    new WPBookfa\WPBF_Public();
}
