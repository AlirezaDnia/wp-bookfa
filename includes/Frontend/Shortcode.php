<?php

namespace Bookfa\Frontend;

class Shortcode
{

    public function __construct()
    {
        add_shortcode('wp_bookfa', [$this, 'render_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
    }

    public function enqueue_frontend_assets()
    {
        // Tailwind CSS برای لایه فرانت
        wp_enqueue_style('tailwindcss-cdn', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.min.css', [], '2.2.19');

        // اسکریپت تعاملی فرانت‌اند
        wp_enqueue_script(
            'wp-bookfa-frontend',
            BOOKFA_URL . 'assets/js/frontend.js',
            [],
            BOOKFA_VERSION,
            true
        );

        wp_localize_script('wp-bookfa-frontend', 'BookfaData', [
            'rest_url' => esc_url_raw(rest_url('bookfa/v1/')),
            'nonce'    => wp_create_nonce('wp_rest'),
        ]);
    }

    public function render_shortcode($atts)
    {
        ob_start();
        require BOOKFA_PATH . 'templates/booking-form.php';
        return ob_get_clean();
    }
}
