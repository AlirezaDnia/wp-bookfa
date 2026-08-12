<?php

namespace Bookfa\API;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class BookingController extends WP_REST_Controller
{

    public function __construct()
    {
        $this->namespace = 'bookfa/v1';
        $this->rest_base = 'bookings';

        // ثبت مسیرها در هنگام init شدن REST API
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes()
    {
        // مسیر دریافت لیست مدرسین: /wp-json/bookfa/v1/bookings/instructors
        register_rest_route($this->namespace, '/' . $this->rest_base . '/instructors', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_instructors'],
                'permission_callback' => '__return_true',
            ],
        ]);

        // مسیر ثبت رزرو جدید: /wp-json/bookfa/v1/bookings
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'create_booking'],
                'permission_callback' => '__return_true',
            ],
        ]);

        // در متد register_routes کلاس BookingController اضافه کنید:
        register_rest_route($this->namespace, '/' . $this->rest_base . '/list', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_bookings_list'],
                'permission_callback' => [$this, 'check_admin_permission'],
            ],
        ]);
    }

    public function get_instructors(WP_REST_Request $request)
    {
        $instructors = \Bookfa\Admin\InstructorSettings::get_all_instructors();

        return new WP_REST_Response([
            'success' => true,
            'data'    => $instructors,
        ], 200);
    }

    public function create_booking(WP_REST_Request $request)
    {
        // منطق ثبت رزرو
        return new WP_REST_Response(['success' => true], 200);
    }

    public function check_admin_permission()
    {
        return current_user_can('edit_posts');
    }

    /**
     * دریافت لیست رزروها با فیلتر مدرس
     */
    public function get_bookings_list(WP_REST_Request $request)
    {
        global $wpdb;

        $instructor_id = absint($request->get_param('instructor_id'));
        $table = $wpdb->prefix . 'bookfa_bookings';

        // اگر کاربر ادمین باشد و مدرس خاصی را انتخاب نکرده باشد، همه رزروها را می‌آورد
        if (current_user_can('manage_options')) {
            if ($instructor_id > 0) {
                $query = $wpdb->prepare("SELECT * FROM $table WHERE instructor_id = %d ORDER BY booking_date DESC, start_time ASC", $instructor_id);
            } else {
                $query = "SELECT * FROM $table ORDER BY booking_date DESC, start_time ASC";
            }
        } else {
            // مدرس عادی فقط رزروهای خودش را می‌بیند
            $current_user_id = get_current_user_id();
            $query = $wpdb->prepare("SELECT * FROM $table WHERE instructor_id = %d ORDER BY booking_date DESC, start_time ASC", $current_user_id);
        }

        $bookings = $wpdb->get_results($query);

        // افزودن نام مدرس به رزروها جهت نمایش به ادمین
        foreach ($bookings as &$b) {
            $user_info = get_userdata($b->instructor_id);
            $b->instructor_name = $user_info ? $user_info->display_name : 'نامشخص';
        }

        return new WP_REST_Response([
            'success'  => true,
            'bookings' => $bookings
        ], 200);
    }
}
