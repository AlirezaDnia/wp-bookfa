document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("bookfa-schedule-form");

    if (form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            // دریافت شناسه مدرس (اگر ادمین باشد از select وگرنه null ارسال می‌شود تا سمت بک‌اند آی‌دی کاربر جاری استفاده شود)
            const instructorSelect = document.getElementById(
                "instructor-select-admin",
            );
            const instructorId = instructorSelect
                ? instructorSelect.value
                : null;

            const schedules = [];
            const days = [6, 0, 1, 2, 3, 4, 5]; // شنبه تا جمعه

            days.forEach((day) => {
                const active = form.querySelector(
                    `[name="active_${day}"]`,
                )?.checked;
                if (active) {
                    schedules.push({
                        day_of_week: day,
                        start_time: form.querySelector(
                            `[name="start_time_${day}"]`,
                        ).value,
                        end_time: form.querySelector(`[name="end_time_${day}"]`)
                            .value,
                        slot_duration: form.querySelector(
                            `[name="slot_${day}"]`,
                        ).value,
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
    }
}
