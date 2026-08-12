<?php
if (!defined('ABSPATH')) exit;
$current_user = wp_get_current_user();
?>

<div class="wrap rtl max-w-6xl my-6 mx-auto px-4 font-sans">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 m-0">مدیریت زمان‌بندی و رزروها</h1>
            <p class="text-gray-500 text-sm mt-1">خوش آمدید، <?php echo esc_html($current_user->display_name); ?></p>
        </div>
        <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-full text-xs font-semibold">WP-Bookfa v1.0</span>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-gray-200 mb-6 space-x-2 space-x-reverse">
        <button id="tab-btn-schedules" onclick="switchTab('schedules')" class="py-3 px-6 text-sm font-medium text-indigo-600 border-b-2 border-indigo-600 focus:outline-none">
            تنظیم شیفت‌های کاری
        </button>
        <button id="tab-btn-bookings" onclick="switchTab('bookings')" class="py-3 px-6 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent focus:outline-none">
            لیست رزروها
        </button>
    </div>

    <!-- Tab 1: Schedules Setup -->
    <div id="tab-schedules" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">تعریف ساعات کاری روزهای هفته</h2>

        <?php if (current_user_can('manage_options')) : ?>
            <!-- بخش انتخاب مدرس اختصاصی مدیر کل -->
            <div class="mb-6 p-4 bg-indigo-50/50 rounded-xl border border-indigo-100 flex items-center justify-between">
                <label for="instructor-select-admin" class="font-bold text-indigo-900 text-sm">انتخاب مدرس جهت مدیریت شیفت‌ها:</label>
                <select id="instructor-select-admin" class="border border-indigo-200 rounded-lg px-3 py-2 text-sm bg-white font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php
                    $instructors = \Bookfa\Admin\InstructorSettings::get_all_instructors();
                    foreach ($instructors as $inst) {
                        echo '<option value="' . esc_attr($inst['id']) . '">' . esc_html($inst['name']) . '</option>';
                    }
                    ?>
                </select>
            </div>
        <?php endif; ?>
        <form id="bookfa-schedule-form" class="space-y-4">
            <?php
            $days = [
                6 => 'شنبه',
                0 => 'یکشنبه',
                1 => 'دوشنبه',
                2 => 'سه‌شنبه',
                3 => 'چهارشنبه',
                4 => 'پنج‌شنبه',
                5 => 'جمعه'
            ];
            foreach ($days as $day_code => $day_name):
            ?>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-100">
                    <div class="w-1/4 font-semibold text-gray-700">
                        <?php echo $day_name; ?>
                    </div>
                    <div class="flex items-center space-x-3 space-x-reverse w-3/4">
                        <label class="text-xs text-gray-500">از:</label>
                        <input type="time" name="start_time_<?php echo $day_code; ?>" value="09:00" class="border border-gray-300 rounded-md px-2 py-1 text-sm">

                        <label class="text-xs text-gray-500">تا:</label>
                        <input type="time" name="end_time_<?php echo $day_code; ?>" value="17:00" class="border border-gray-300 rounded-md px-2 py-1 text-sm">

                        <label class="text-xs text-gray-500 me-2">مدت هر جلسه (دقیقه):</label>
                        <select name="slot_<?php echo $day_code; ?>" class="border border-gray-300 rounded-md px-2 py-1 text-sm">
                            <option value="30">30 دقیقه</option>
                            <option value="45">45 دقیقه</option>
                            <option value="60">1 ساعت</option>
                        </select>

                        <label class="inline-flex items-center me-4 cursor-pointer">
                            <input type="checkbox" name="active_<?php echo $day_code; ?>" value="1" checked class="form-checkbox h-4 w-4 text-indigo-600 rounded">
                            <span class="mr-2 text-xs text-gray-600">فعال</span>
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2 rounded-lg text-sm transition">
                    ذخیره تنظیمات
                </button>
            </div>
        </form>
    </div>

    <!-- Tab 2: Bookings List -->
    <!-- Tab 2: Bookings List -->
    <div id="tab-bookings" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hidden">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold text-gray-800">لیست رزروهای ثبت‌شده</h2>

            <?php if (current_user_can('manage_options')) : ?>
                <div class="flex items-center gap-2">
                    <label for="filter-instructor-bookings" class="text-xs font-semibold text-gray-500">فیلتر مدرس:</label>
                    <select id="filter-instructor-bookings" onchange="loadBookings()" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs bg-white font-medium outline-none">
                        <option value="0">همه مدرسین</option>
                        <?php
                        $instructors = \Bookfa\Admin\InstructorSettings::get_all_instructors();
                        foreach ($instructors as $inst) {
                            echo '<option value="' . esc_attr($inst['id']) . '">' . esc_html($inst['name']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            <?php endif; ?>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 font-bold border-b">
                    <tr>
                        <th class="p-3">#</th>
                        <th class="p-3">مدرس / مشاور</th>
                        <th class="p-3">نام مشتری</th>
                        <th class="p-3">تلفن تماس</th>
                        <th class="p-3">تاریخ رزرو</th>
                        <th class="p-3">ساعت</th>
                        <th class="p-3">وضعیت</th>
                    </tr>
                </thead>
                <tbody id="bookings-table-body">
                    <tr>
                        <td colspan="7" class="p-4 text-center text-gray-400">در حال بارگذاری...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>