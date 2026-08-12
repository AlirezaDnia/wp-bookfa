<?php

namespace Bookfa\API;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class BookingController extends WP_REST_Controller
{

    public function __construct()
    {
        $this->namespace = 'bookfa/v1';
        $this->rest_base = 'bookings';
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes()
    {
        // ثبت رزرو جدید توسط کاربر
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'create_booking'],
                'permission_callback' => '__return_true', // عمومی برای کاربران
                'args'                => $this->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE),
            ],
        ]);

        // دریافت لیست مدرسین
        register_rest_route($this->namespace, '/instructors', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_instructors'],
                'permission_callback' => '__return_true',
            ],
        ]);
    }

    /**
     * دریافت لیست مدرسین/مشاورین
     */
    public function get_instructors(WP_REST_Request $request)
    {
        $args = [
            'role__in' => ['administrator', 'editor', 'author'], // یا نقش اختصاصی instructor
            'number'   => 100,
            'fields'   => ['ID', 'display_name', 'user_email'],
        ];

        $users = get_users($args);
        $instructors = array_map(function ($user) {
            return [
                'id'     => $user->ID,
                'name'   => $user->display_name,
                'avatar' => get_avatar_url($user->ID, ['size' => 128]),
            ];
        }, $users);

        return new WP_REST_Response([
            'success' => true,
            'data'    => $instructors,
        ], 200);
    }

    /**
     * ثبت رزرو جدید همراه با جلوگیری از رزرو تکراری
     */
    public function create_booking(WP_REST_Request $request)
    {
        global $wpdb;

        $instructor_id  = absint($request->get_param('instructor_id'));
        $customer_name  = sanitize_text_field($request->get_param('customer_name'));
        $customer_phone = sanitize_text_field($request->get_param('customer_phone'));
        $booking_date   = sanitize_text_field($request->get_param('booking_date')); // فرمت YYYY-MM-DD
        $start_time     = sanitize_text_field($request->get_param('start_time'));   // فرمت HH:MM:SS
        $end_time       = sanitize_text_field($request->get_param('end_time'));     // فرمت HH:MM:SS

        // اعتبارسنجی ورودی‌ها
        if (empty($instructor_id) || empty($customer_name) || empty($customer_phone) || empty($booking_date) || empty($start_time)) {
            return new WP_Error('missing_fields', 'لطفاً تمامی فیلدهای الزامی را تکمیل کنید.', ['status' => 400]);
        }

        $table_bookings = $wpdb->prefix . 'bookfa_bookings';

        // بررسی عدم تداخل زمان (Double Booking Check)
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_bookings 
             WHERE instructor_id = %d 
             AND booking_date = %s 
             AND start_time = %s 
             AND status != 'cancelled'",
            $instructor_id,
            $booking_date,
            $start_time
        ));

        if ($existing > 0) {
            return new WP_Error('slot_taken', 'این زمان قبلاً توسط شخص دیگری رزرو شده است.', ['status' => 409]);
        }

        // ثبت در دیتابیس
        $inserted = $wpdb->insert(
            $table_bookings,
            [
                'instructor_id'  => $instructor_id,
                'customer_name'  => $customer_name,
                'customer_phone' => $customer_phone,
                'booking_date'   => $booking_date,
                'start_time'     => $start_time,
                'end_time'       => $end_time,
                'status'         => 'confirmed',
            ],
            ['%d', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        if (!$inserted) {
            return new WP_Error('db_error', 'خطا در ثبت رزرو در دیتابیس.', ['status' => 500]);
        }

        return new WP_REST_Response([
            'success'    => true,
            'message'    => 'رزرو شما با موفقیت ثبت شد.',
            'booking_id' => $wpdb->insert_id,
        ], 201);
    }
}
