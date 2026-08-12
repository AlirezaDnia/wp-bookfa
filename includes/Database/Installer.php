<?php

namespace Bookfa\Database;

class Installer
{
    public static function create_tables()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // جدول زمان‌های کاری مدرسین
        $table_availability = $wpdb->prefix . 'bookfa_availability';
        $sql_availability = "CREATE TABLE IF NOT EXISTS $table_availability (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            instructor_id BIGINT(20) UNSIGNED NOT NULL,
            day_of_week TINYINT(1) NOT NULL, -- 0 (یکشنبه) تا 6 (شنبه)
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            slot_duration INT UNSIGNED NOT NULL DEFAULT 30, -- دقیقه
            is_active TINYINT(1) DEFAULT 1,
            PRIMARY KEY  (id),
            KEY instructor_id (instructor_id)
        ) $charset_collate;";

        // جدول رزروها
        $table_bookings = $wpdb->prefix . 'bookfa_bookings';
        $sql_bookings = "CREATE TABLE IF NOT EXISTS $table_bookings (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            instructor_id BIGINT(20) UNSIGNED NOT NULL,
            customer_name VARCHAR(191) NOT NULL,
            customer_phone VARCHAR(20) NOT NULL,
            booking_date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'pending', -- pending, confirmed, cancelled
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY instructor_date (instructor_id, booking_date)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_availability);
        dbDelta($sql_bookings);
    }
}
