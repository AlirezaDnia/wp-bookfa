<?php

namespace Bookfa\Admin;

class AdminMenu
{

    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function register_admin_menu()
    {
        add_menu_page(
            'مدیریت رزروها - بوک‌فا',
            'بوک‌فا (رزرو)',
            'edit_posts',
            'wp-bookfa',
            [$this, 'render_admin_page'],
            'dashicons-calendar-alt',
            25
        );
    }

    public function enqueue_admin_assets($hook)
    {
        if ($hook !== 'toplevel_page_wp-bookfa') {
            return;
        }

        // استایل Tailwind (CDN یا بیلد شده) + FontAwesome برای آیکون‌ها
        wp_enqueue_style('tailwindcss', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.min.css', [], '2.2.19');

        // جاوااسکریپت تعاملی پنل ادمین
        wp_enqueue_script(
            'wp-bookfa-admin',
            BOOKFA_URL . 'assets/js/admin.js',
            ['jquery'],
            BOOKFA_VERSION,
            true
        );

        // ارسال API Nonce و URL به فایل JS
        wp_localize_script('wp-bookfa-admin', 'BookfaAdmin', [
            'rest_url' => esc_url_raw(rest_url('bookfa/v1/')),
            'nonce'    => wp_create_nonce('wp_rest'),
        ]);
    }

    public function render_admin_page()
    {
        require_once BOOKFA_PATH . 'templates/admin-page.php';
    }
}
