<?php

namespace WPBookfa;

if (! defined('ABSPATH')) {
    exit;
}

class WPBF_Activator
{

    public static function activate(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpbf_bookings';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            mentor_id bigint(20) NOT NULL,
            user_name varchar(100) NOT NULL,
            user_email varchar(100) NOT NULL,
            booking_date date NOT NULL,
            booking_time varchar(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
