<?php

namespace WPBookfa;

if (! defined('ABSPATH')) {
    exit;
}

class WPBF_Admin
{

    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_admin_menu(): void
    {
        add_menu_page(
            'تنظیمات wp-bookfa',
            'رزرو نوبت (Bookfa)',
            'manage_options',
            'wp-bookfa-settings',
            [$this, 'render_admin_page'],
            'dashicons-calendar-alt',
            25
        );
    }

    public function register_settings(): void
    {
        register_setting('wpbf_settings_group', 'wpbf_allowed_mentors');
    }

    public function render_admin_page(): void
    {
        $users = get_users(['fields' => ['ID', 'display_name']]);
        $saved_mentors = get_option('wpbf_allowed_mentors', []);
        if (! is_array($saved_mentors)) {
            $saved_mentors = [];
        }
?>
        <div class="wrap" style="direction: rtl;">
            <h1>تنظیمات افزونه wp-bookfa</h1>
            <form method="post" action="options.php">
                <?php settings_fields('wpbf_settings_group'); ?>
                <?php do_settings_sections('wpbf_settings_group'); ?>

                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><label>انتخاب مدرسین / مشاورین مجاز:</label></th>
                            <td>
                                <fieldset>
                                    <?php foreach ($users as $user) : ?>
                                        <label style="display: block; margin-bottom: 8px;">
                                            <input type="checkbox" name="wpbf_allowed_mentors[]" value="<?php echo esc_attr($user->ID); ?>" <?php checked(in_array($user->ID, $saved_mentors)); ?> />
                                            <?php echo esc_html($user->display_name); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </fieldset>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php submit_button('ذخیره تنظیمات'); ?>
            </form>
        </div>
<?php
    }
}
