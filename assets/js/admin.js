document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("bookfa-schedule-form");
    const instructorSelect = document.getElementById("instructor-select-admin");

    // ۱. اگر ادمین مدرس را تغییر داد، تنظیمات شیفت آن مدرس لود شود
    if (instructorSelect) {
        instructorSelect.addEventListener("change", (e) => {
            loadInstructorSchedule(e.target.value);
        });

        // لود اولیه شیفت‌های اولین مدرس در لیست
        if (instructorSelect.value) {
            loadInstructorSchedule(instructorSelect.value);
        }
    } else {
        // اگر مدرس عادی است، شیفت‌های خودش لود شود
        loadInstructorSchedule();
    }

    // ۲. ثبت/ذخیره شیفت‌های کاری
    if (form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            const instructorId = instructorSelect
                ? instructorSelect.value
                : null;
            const schedules = [];
            const days = [6, 0, 1, 2, 3, 4, 5]; // شنبه (6) تا جمعه (5)

            days.forEach((day) => {
                const activeInput = form.querySelector(
                    `[name="active_${day}"]`,
                );
                const active = activeInput ? activeInput.checked : false;

                if (active) {
                    schedules.push({
                        day_of_week: day,
                        start_time:
                            form.querySelector(`[name="start_time_${day}"]`)
                                ?.value || "09:00",
                        end_time:
                            form.querySelector(`[name="end_time_${day}"]`)
                                ?.value || "17:00",
                        slot_duration:
                            form.querySelector(`[name="slot_${day}"]`)?.value ||
                            30,
                    });
                }
            });

            try {
                const response = await fetch(
                    BookfaAdmin.rest_url + "availability/settings",
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-WP-Nonce": BookfaAdmin.nonce,
                        },
                        body: JSON.stringify({
                            instructor_id: instructorId,
                            schedules: schedules,
                        }),
                    },
                );

                const data = await response.json();
                if (data.success) {
                    alert("تنظیمات زمان‌بندی با موفقیت ذخیره شد.");
                } else {
                    alert("خطا: " + (data.message || "مشکلی پیش آمد."));
                }
            } catch (err) {
                console.error(err);
                alert("خطا در ارتباط با سرور.");
            }
        });
    }
});

/**
 * دریافت و پر کردن تنظیمات شیفت کاری یک مدرس
 */
async function loadInstructorSchedule(instructorId = null) {
    const form = document.getElementById("bookfa-schedule-form");
    if (!form) return;

    try {
        const url = instructorId
            ? `${BookfaAdmin.rest_url}availability/settings?instructor_id=${instructorId}`
            : `${BookfaAdmin.rest_url}availability/settings`;

        const response = await fetch(url, {
            headers: { "X-WP-Nonce": BookfaAdmin.nonce },
        });
        const data = await response.json();

        // ریست کردن فرم قبل از پر کردن مجدد
        const days = [6, 0, 1, 2, 3, 4, 5];
        days.forEach((day) => {
            const activeCheckbox = form.querySelector(`[name="active_${day}"]`);
            if (activeCheckbox) activeCheckbox.checked = false;
        });

        if (data.success && Array.isArray(data.schedules)) {
            data.schedules.forEach((item) => {
                const day = item.day_of_week;
                const activeCheckbox = form.querySelector(
                    `[name="active_${day}"]`,
                );
                const startTimeInput = form.querySelector(
                    `[name="start_time_${day}"]`,
                );
                const endTimeInput = form.querySelector(
                    `[name="end_time_${day}"]`,
                );
                const slotSelect = form.querySelector(`[name="slot_${day}"]`);

                if (activeCheckbox) activeCheckbox.checked = true;
                if (startTimeInput && item.start_time)
                    startTimeInput.value = item.start_time;
                if (endTimeInput && item.end_time)
                    endTimeInput.value = item.end_time;
                if (slotSelect && item.slot_duration)
                    slotSelect.value = item.slot_duration;
            });
        }
    } catch (err) {
        console.error("خطا در دریافت تنظیمات شیفت:", err);
    }
}

/**
 * دریافت و نمایش لیست رزروها
 */
async function loadBookings() {
    const tbody = document.getElementById("bookings-table-body");
    if (!tbody) return;

    tbody.innerHTML =
        '<tr><td colspan="7" class="p-4 text-center text-gray-400">در حال بارگذاری...</td></tr>';

    const filterSelect = document.getElementById("filter-instructor-bookings");
    const instructorId = filterSelect ? filterSelect.value : 0;

    try {
        const response = await fetch(
            `${BookfaAdmin.rest_url}bookings/list?instructor_id=${instructorId}`,
            {
                headers: { "X-WP-Nonce": BookfaAdmin.nonce },
            },
        );
        const data = await response.json();

        if (data.success && data.bookings && data.bookings.length > 0) {
            tbody.innerHTML = data.bookings
                .map(
                    (b, index) => `
                <tr class="border-b hover:bg-gray-50/50 transition">
                    <td class="p-3 font-semibold">${index + 1}</td>
                    <td class="p-3 font-bold text-indigo-600">${b.instructor_name || "—"}</td>
                    <td class="p-3 text-gray-800">${b.customer_name}</td>
                    <td class="p-3 dir-ltr text-right">${b.customer_phone}</td>
                    <td class="p-3">${b.booking_date}</td>
                    <td class="p-3">${b.start_time.substr(0, 5)} - ${b.end_time.substr(0, 5)}</td>
                    <td class="p-3">
                        <span class="bg-emerald-50 text-emerald-600 px-2 py-1 rounded-md text-xs font-bold">تایید شده</span>
                    </td>
                </tr>
            `,
                )
                .join("");
        } else {
            tbody.innerHTML =
                '<tr><td colspan="7" class="p-4 text-center text-gray-400">هیچ رزروی یافت نشد.</td></tr>';
        }
    } catch (e) {
        console.error(e);
        tbody.innerHTML =
            '<tr><td colspan="7" class="p-4 text-center text-red-400">خطا در دریافت اطلاعات.</td></tr>';
    }
}

/**
 * سوییچ بین تب‌ها
 */
function switchTab(tab) {
    const schedulesTab = document.getElementById("tab-schedules");
    const bookingsTab = document.getElementById("tab-bookings");
    const btnSchedules = document.getElementById("tab-btn-schedules");
    const btnBookings = document.getElementById("tab-btn-bookings");

    if (tab === "schedules") {
        schedulesTab.classList.remove("hidden");
        bookingsTab.classList.add("hidden");
        btnSchedules.className =
            "py-3 px-6 text-sm font-medium text-indigo-600 border-b-2 border-indigo-600 focus:outline-none";
        btnBookings.className =
            "py-3 px-6 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent focus:outline-none";
    } else {
        schedulesTab.classList.add("hidden");
        bookingsTab.classList.remove("hidden");
        btnBookings.className =
            "py-3 px-6 text-sm font-medium text-indigo-600 border-b-2 border-indigo-600 focus:outline-none";
        btnSchedules.className =
            "py-3 px-6 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent focus:outline-none";

        // بارگذاری لیست رزروها هنگام ورود به تب
        loadBookings();
    }
}
