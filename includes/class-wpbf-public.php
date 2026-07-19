<?php

namespace WPBookfa;

use Morilog\Jalali\Jalalian;

if (! defined('ABSPATH')) {
    exit;
}

class WPBF_Public
{

    public function __construct()
    {
        add_shortcode('wp_bookfa_form', [$this, 'render_booking_form']);
        add_action('admin_post_nopriv_submit_wpbf_booking', [$this, 'handle_booking_submission']);
        add_action('admin_post_submit_wpbf_booking', [$this, 'handle_booking_submission']);
    }

    public function render_booking_form(): string
    {
        $mentors = get_option('wpbf_allowed_mentors', []);
        if (empty($mentors)) {
            return '<p>هیچ مدرسی در تنظیمات wp-bookfa تعریف نشده است.</p>';
        }

        ob_start();
?>
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" style="direction: rtl; text-align: right; max-width: 500px; margin: 0 auto;">
            <input type="hidden" name="action" value="submit_wpbf_booking">
            <?php wp_nonce_field('wpbf_booking_nonce_action', 'wpbf_nonce'); ?>

            <p>
                <label for="wpbf_mentor">انتخاب مشاور / مدرس:</label>
                <select name="wpbf_mentor" id="wpbf_mentor" required style="width:100%; padding: 8px;">
                    <?php foreach ($mentors as $mentor_id) :
                        $user_info = get_userdata($mentor_id);
                        if ($user_info) : ?>
                            <option value="' . esc_attr( $mentor_id ) . '"><?php echo esc_html($user_info->display_name); ?></option>
                    <?php endif;
                    endforeach; ?>
                </select>
            </p>

            <p>
                <label for="wpbf_date">انتخاب روز (شمسی):</label>
                <select name="wpbf_date" id="wpbf_date" required style="width:100%; padding: 8px;">
                    <?php
                    for ($i = 0; $i < 7; $i++) {
                        $instance = Jalalian::now()->addDays($i);
                        $gregorian_date = $instance->toCarbon()->format('Y-m-d');
                        $jalali_format = $instance->format('%A %d %B %Y');
                        echo '<option value="' . esc_attr($gregorian_date) . '">' . esc_html($jalali_format) . '</option>';
                    }
                    ?>
                </select>
            </p>

            <p>
                <label for="wpbf_time">انتخاب ساعت:</label>
                <select name="wpbf_time" id="wpbf_time" required style="width:100%; padding: 8px;">
                    <option value="09:00-10:00">ساعت ۰۹:۰۰ الی ۱۰:۰۰</option>
                    <option value="11:00-12:00">ساعت ۱۱:۰۰ الی ۱۲:۰۰</option>
                    <option value="15:00-16:00">ساعت ۱۵:۰۰ الی ۱۶:۰۰</option>
                    <option value="17:00-18:00">ساعت ۱۷:۰۰ الی ۱۸:۰۰</option>
                </select>
            </p>

            <p>
                <label for="wpbf_name">نام و نام خانوادگی:</label>
                <input type="text" name="wpbf_name" id="wpbf_name" required style="width:100%; padding: 8px;">
            </p>

            <p>
                <label for="wpbf_email">ایمیل شما:</label>
                <input type="email" name="wpbf_email" id="wpbf_email" required style="width:100%; padding: 8px;">
            </p>

            <p>
                <button type="submit" style="padding: 10px 20px; background: #0073aa; color: #fff; border: none; cursor: pointer;">ثبت و رزرو نوبت</button>
            </p>
        </form>
<?php
        return ob_get_clean();
    }

    public function handle_booking_submission(): void
    {
        if (! isset($_POST['wpbf_nonce']) || ! wp_verify_nonce($_POST['wpbf_nonce'], 'wpbf_booking_nonce_action')) {
            wp_die('دسترسی غیرمجاز شناسایی شد.');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wpbf_bookings';

        $mentor_id = intval($_POST['wpbf_mentor']);
        $date      = sanitize_text_field($_POST['wpbf_date']);
        $time      = sanitize_text_field($_POST['wpbf_time']);
        $name      = sanitize_text_field($_POST['wpbf_name']);
        $email     = sanitize_email($_POST['wpbf_email']);

        if (empty($mentor_id) || empty($date) || empty($time) || empty($name) || ! is_email($email)) {
            wp_die('لطفا تمامی فیلدها را به درستی مقداردهی کنید.');
        }

        $inserted = $wpdb->insert(
            $table_name,
            [
                'mentor_id'    => $mentor_id,
                'user_name'    => $name,
                'user_email'   => $email,
                'booking_date' => $date,
                'booking_time' => $time
            ],
            ['%d', '%s', '%s', '%s', '%s']
        );

        if ($inserted) {
            $jalali_date = Jalalian::fromCarbon(\Carbon\Carbon::parse($date))->format('%Y/%m/%d');

            WPBF_Mailer::send_notifications($mentor_id, $name, $email, $jalali_date, $time);

            wp_redirect(add_query_arg('booking', 'success', wp_get_referer()));
            exit;
        }

        wp_die('خطا در ثبت اطلاعات، مجددا تلاش کنید.');
    }
}
