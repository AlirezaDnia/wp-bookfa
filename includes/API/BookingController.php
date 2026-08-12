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
}
