<?php

namespace Bookfa\API;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class AvailabilityController extends WP_REST_Controller
{

    public function __construct()
    {
        $this->namespace = 'bookfa/v1';
        $this->rest_base = 'availability';
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes()
    {
        // دریافت زمان‌های کاری/خالی یک مدرس در یک تاریخ مشخص
        register_rest_route($this->namespace, '/' . $this->rest_base . '/slots', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_available_slots'],
                'permission_callback' => '__return_true',
            ],
        ]);

        // ذخیره زمان‌های کاری مدرس (مخصوص پنل ادمین / مدرس)
        register_rest_route($this->namespace, '/' . $this->rest_base . '/settings', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'save_availability'],
                'permission_callback' => [$this, 'check_admin_permission'],
            ],
        ]);
    }

    public function check_admin_permission()
    {
        return current_user_can('edit_posts');
    }

    /**
     * محاسبه و بازگرداندن سانس‌های (Slots) خالی مدرس بر اساس روز هفته و رزروهای قبلی
     */
    public function get_available_slots(WP_REST_Request $request)
    {
        global $wpdb;

        $instructor_id = absint($request->get_param('instructor_id'));
        $date          = sanitize_text_field($request->get_param('date')); // YYYY-MM-DD

        if (empty($instructor_id) || empty($date)) {
            return new WP_Error('invalid_params', 'شناسه مدرس و تاریخ الزامی هستند.', ['status' => 400]);
        }

        // محاسبه روز هفته (0 = یکشنبه, 6 = شنبه)
        $day_of_week = date('w', strtotime($date));

        // 1. دریافت زمان‌های کاری تنظیم شده مدرس برای این روز هفته
        $table_availability = $wpdb->prefix . 'bookfa_availability';
        $rules = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_availability WHERE instructor_id = %d AND day_of_week = %d AND is_active = 1",
            $instructor_id,
            $day_of_week
        ));

        if (empty($rules)) {
            return new WP_REST_Response(['success' => true, 'slots' => []], 200);
        }

        // 2. دریافت رزروهای ثبت‌شده قبلی در همین تاریخ
        $table_bookings = $wpdb->prefix . 'bookfa_bookings';
        $booked_slots = $wpdb->get_col($wpdb->prepare(
            "SELECT start_time FROM $table_bookings WHERE instructor_id = %d AND booking_date = %s AND status != 'cancelled'",
            $instructor_id,
            $date
        ));

        $available_slots = [];

        // 3. تقسیم‌بندی زمان کاری به سانس‌های مشخص (مثلاً ۳۰ دقیقه‌ای)
        foreach ($rules as $rule) {
            $start    = strtotime($rule->start_time);
            $end      = strtotime($rule->end_time);
            $interval = $rule->slot_duration * 60; // تبدیل دقیقه به ثانیه

            for ($time = $start; $time + $interval <= $end; $time += $interval) {
                $time_str     = date('H:i:s', $time);
                $end_time_str = date('H:i:s', $time + $interval);

                // اگر سانس قبلاً رزرو نشده باشد، اضافه می‌شود
                if (!in_array($time_str, $booked_slots)) {
                    $available_slots[] = [
                        'start_time' => date('H:i', $time),
                        'end_time'   => date('H:i', $time + $interval),
                        'raw_start'  => $time_str,
                        'raw_end'    => $end_time_str,
                    ];
                }
            }
        }

        return new WP_REST_Response([
            'success' => true,
            'slots'   => $available_slots,
        ], 200);
    }

    /**
     * ذخیره/به‌روزرسانی شیفت‌های کاری مدرس
     */
    public function save_availability(WP_REST_Request $request)
    {
        global $wpdb;

        // اگر ادمین آی‌دی مدرس را فرستاده بود از آن استفاده کن، در غیر این صورت کاربر فعلی
        $instructor_id = absint($request->get_param('instructor_id'));
        if (empty($instructor_id) || !current_user_can('manage_options')) {
            $instructor_id = get_current_user_id();
        }

        $schedules = $request->get_param('schedules'); // آرایه‌ای از روزها و ساعت‌ها

        if (empty($instructor_id) || !is_array($schedules)) {
            return new WP_Error('invalid_data', 'شناسه مدرس یا فرمت داده‌ها نادرست است.', ['status' => 400]);
        }

        $table = $wpdb->prefix . 'bookfa_availability';

        // پاکسازی زمان‌های قبلی جهت جایگزینی
        $wpdb->delete($table, ['instructor_id' => $instructor_id], ['%d']);

        foreach ($schedules as $sched) {
            $wpdb->insert($table, [
                'instructor_id' => $instructor_id,
                'day_of_week'   => absint($sched['day_of_week']),
                'start_time'    => sanitize_text_field($sched['start_time']),
                'end_time'      => sanitize_text_field($sched['end_time']),
                'slot_duration' => absint($sched['slot_duration'] ?? 30),
                'is_active'     => 1,
            ]);
        }

        return new WP_REST_Response(['success' => true, 'message' => 'تنظیمات زمان‌بندی ذخیره شد.'], 200);
    }
}
