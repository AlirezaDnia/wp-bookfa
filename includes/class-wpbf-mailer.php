<?php

namespace WPBookfa;

if (! defined('ABSPATH')) {
    exit;
}

class WPBF_Mailer
{

    public static function send_notifications(int $mentor_id, string $user_name, string $user_email, string $jalali_date, string $time): void
    {
        $mentor_info = get_userdata($mentor_id);
        $site_name   = get_bloginfo('name');

        $headers = ['Content-Type: text/html; charset=UTF-8'];

        // ۱. ایمیل برای کاربر رزرو کننده
        $user_subject = "تایید رزرو نوبت در {$site_name}";
        $user_message = "
            <h3>کاربر گرامی {$user_name}، سلام.</h3>
            <p>نوبت شما با موفقیت در سیستم wp-bookfa ثبت شد.</p>
            <p><strong>استاد/مشاور:</strong> {$mentor_info->display_name}</p>
            <p><strong>تاریخ:</strong> {$jalali_date}</p>
            <p><strong>ساعت:</strong> {$time}</p>
            <hr>
            <p>با تشکر، {$site_name}</p>
        ";
        wp_mail($user_email, $user_subject, $user_message, $headers);

        // ۲. ایمیل برای مدرس/مشاور
        if ($mentor_info) {
            $mentor_subject = "نوبت رزرو جدید در {$site_name} (wp-bookfa)";
            $mentor_message = "
                <h3>استاد گرامی {$mentor_info->display_name}، سلام.</h3>
                <p>یک نوبت جدید برای شما رزرو شده است:</p>
                <p><strong>نام دانشجو:</strong> {$user_name}</p>
                <p><strong>ایمیل دانشجو:</strong> {$user_email}</p>
                <p><strong>تاریخ نوبت:</strong> {$jalali_date}</p>
                <p><strong>ساعت نوبت:</strong> {$time}</p>
            ";
            wp_mail($mentor_info->user_email, $mentor_subject, $mentor_message, $headers);
        }
    }
}
