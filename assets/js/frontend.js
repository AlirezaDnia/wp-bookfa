document.addEventListener("DOMContentLoaded", () => {
    let state = {
        instructor_id: null,
        date: null,
        start_time: null,
        end_time: null,
    };

    fetchInstructors();

    // 1. Fetch Instructors
    async function fetchInstructors() {
        try {
            const res = await fetch(
                BookfaData.rest_url + "bookings/instructors",
            );
            const data = await res.json();

            const container = document.getElementById("instructors-list");
            if (data.success && data.data.length > 0) {
                container.innerHTML = data.data
                    .map(
                        (inst) => `
                    <div onclick="selectInstructor(${inst.id})" class="p-4 border-2 border-gray-100 hover:border-indigo-500 rounded-xl cursor-pointer transition flex items-center space-x-3 space-x-reverse bg-gray-50 hover:bg-indigo-50/30">
                        <img src="${inst.avatar}" class="w-12 h-12 rounded-full border">
                        <div>
                            <div class="font-bold text-gray-800 text-sm">${inst.name}</div>
                            <div class="text-xs text-indigo-600 font-medium mt-0.5">انتخاب و ادامه ←</div>
                        </div>
                    </div>
                `,
                    )
                    .join("");
            } else {
                container.innerHTML =
                    '<div class="text-red-500 text-center">مدرسی یافت نشد.</div>';
            }
        } catch (e) {
            console.error(e);
        }
    }

    // Select Instructor
    window.selectInstructor = function (id) {
        state.instructor_id = id;
        goToStep(2);
    };

    // 2. Fetch Slots
    document
        .getElementById("btn-to-step-3")
        ?.addEventListener("click", async () => {
            const dateInput =
                document.getElementById("booking-date-input").value;
            if (!dateInput) {
                alert("لطفاً یک تاریخ را انتخاب کنید.");
                return;
            }
            state.date = dateInput;

            try {
                const res = await fetch(
                    `${BookfaData.rest_url}availability/slots?instructor_id=${state.instructor_id}&date=${state.date}`,
                );
                const data = await res.json();

                const slotsContainer =
                    document.getElementById("slots-container");
                if (data.success && data.slots.length > 0) {
                    slotsContainer.innerHTML = data.slots
                        .map(
                            (s) => `
                    <button onclick="selectSlot('${s.raw_start}', '${s.raw_end}')" class="p-3 text-sm font-semibold border rounded-xl border-gray-200 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition">
                        ${s.start_time}
                    </button>
                `,
                        )
                        .join("");
                    goToStep(3);
                } else {
                    alert("برای این تاریخ هیچ زمان خالی یافت نشد.");
                }
            } catch (e) {
                console.error(e);
            }
        });

    // Select Slot
    window.selectSlot = function (start, end) {
        state.start_time = start;
        state.end_time = end;
        goToStep(4);
    };

    // 3. Final Submit
    document
        .getElementById("booking-final-form")
        ?.addEventListener("submit", async (e) => {
            e.preventDefault();

            const btn = document.getElementById("submit-booking-btn");
            btn.disabled = true;
            btn.innerText = "در حال ثبت...";

            const payload = {
                instructor_id: state.instructor_id,
                booking_date: state.date,
                start_time: state.start_time,
                end_time: state.end_time,
                customer_name: document.getElementById("cust-name").value,
                customer_phone: document.getElementById("cust-phone").value,
            };

            try {
                const res = await fetch(BookfaData.rest_url + "bookings", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();
                if (data.success) {
                    goToStep("success");
                } else {
                    alert("خطا: " + data.message);
                    btn.disabled = false;
                    btn.innerText = "تأیید و ثبت نهایی رزرو";
                }
            } catch (e) {
                console.error(e);
                alert("خطا در ثبت رزرو.");
                btn.disabled = false;
            }
        });

    function goToStep(step) {
        document
            .querySelectorAll(".wizard-step")
            .forEach((el) => el.classList.add("hidden"));
        const target = document.getElementById(`step-${step}`);
        if (target) target.classList.remove("hidden");

        // Update Indicators
        if (typeof step === "number") {
            document.querySelectorAll(".step-indicator").forEach((ind, i) => {
                if (i + 1 <= step) {
                    ind.classList.add("text-indigo-600", "font-bold");
                    ind.classList.remove("text-gray-400");
                }
            });
        }
    }
});
