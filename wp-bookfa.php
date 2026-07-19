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

// ۱. لود وابستگی‌های خارجی کامپوزر (کتابخانه تاریخ شمسی)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    add_action('admin_notices', function () {
        echo '<div class="error"><p>لطفاً ابتدا دستور <code>composer install</code> را در پوشه افزونه wp-bookfa اجرا کنید.</p></div>';
    });
    return;
}

// ۲. لود مستقیم تمام کلاس‌های داخلی افزونه برای جلوگیری از خطای ناپدید شدن کلاس‌ها
require_once __DIR__ . '/includes/class-wpbf-activator.php';
require_once __DIR__ . '/includes/class-wpbf-admin.php';
require_once __DIR__ . '/includes/class-wpbf-public.php';
require_once __DIR__ . '/includes/class-wpbf-mailer.php';

// ۳. ثبت هوک فعال‌سازی
register_activation_hook(__FILE__, ['WPBookfa\WPBF_Activator', 'activate']);

// ۴. راه‌اندازی ماژول‌های اصلی
add_action('plugins_loaded', 'wpbf_init_plugin');

function wpbf_init_plugin(): void
{
    if (is_admin()) {
        new WPBookfa\WPBF_Admin();
    }
    new WPBookfa\WPBF_Public();
}
