<?php

namespace Bookfa\Admin;

class InstructorSettings
{

    public function __construct()
    {
        // ثبت نقش کاربری در زمان فعال‌سازی افزونه
        add_action('init', [$this, 'register_instructor_role']);
    }

    /**
     * ثبت نقش کاربری سفارشی مدرس / مشاور
     */
    public function register_instructor_role()
    {
        if (!get_role('wp_instructor')) {
            add_role('wp_instructor', 'مدرس / مشاور', [
                'read'         => true,
                'edit_posts'   => false,
                'upload_files' => true,
            ]);
        }
    }

    /**
     * دریافت لیست فقط مدرسین (کاربران دارای نقش wp_instructor)
     */
    public static function get_all_instructors()
    {
        $users = get_users([
            'role'   => 'wp_instructor',
            'number' => 100,
            'fields' => ['ID', 'display_name', 'user_email'],
        ]);

        return array_map(function ($user) {
            return [
                'id'     => $user->ID,
                'name'   => $user->display_name ? $user->display_name : $user->user_email,
                'avatar' => get_avatar_url($user->ID, ['size' => 128]),
            ];
        }, $users);
    }
}
