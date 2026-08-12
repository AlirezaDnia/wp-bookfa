<?php if (!defined('ABSPATH')) exit; ?>

<div id="bookfa-wizard" class="max-w-2xl mx-auto my-8 bg-white rounded-2xl shadow-xl border border-gray-100 p-6 md:p-8 font-sans text-right rtl">
    <!-- Progress Indicator -->
    <div class="flex justify-between items-center mb-8 border-b border-gray-100 pb-4">
        <div class="step-indicator active text-indigo-600 font-bold text-sm flex items-center gap-2" id="ind-step-1">
            <span class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs">۱</span>
            مدرس
        </div>
        <div class="step-indicator text-gray-400 font-medium text-sm flex items-center gap-2" id="ind-step-2">
            <span class="w-7 h-7 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-xs">۲</span>
            تاریخ
        </div>
        <div class="step-indicator text-gray-400 font-medium text-sm flex items-center gap-2" id="ind-step-3">
            <span class="w-7 h-7 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-xs">۳</span>
            ساعت
        </div>
        <div class="step-indicator text-gray-400 font-medium text-sm flex items-center gap-2" id="ind-step-4">
            <span class="w-7 h-7 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-xs">۴</span>
            مشخصات
        </div>
    </div>

    <!-- Step 1: Select Instructor -->
    <div id="step-1" class="wizard-step">
        <h3 class="text-lg font-bold text-gray-800 mb-4">لطفاً مدرس یا مشاور خود را انتخاب کنید:</h3>
        <div id="instructors-list" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="text-center text-gray-400 py-6">در حال بارگذاری لیست مدرسین...</div>
        </div>
    </div>

    <!-- Step 2: Select Date -->
    <div id="step-2" class="wizard-step hidden">
        <h3 class="text-lg font-bold text-gray-800 mb-4">تاریخ مورد نظر را مشخص کنید:</h3>
        <input type="date" id="booking-date-input" min="<?php echo date('Y-m-d'); ?>" class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
        <button id="btn-to-step-3" class="mt-6 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-indigo-100">
            جستجوی سانس‌های خالی
        </button>
    </div>

    <!-- Step 3: Select Slot -->
    <div id="step-3" class="wizard-step hidden">
        <h3 class="text-lg font-bold text-gray-800 mb-4">ساعت حضور را انتخاب کنید:</h3>
        <div id="slots-container" class="grid grid-cols-3 sm:grid-cols-4 gap-3">
            <!-- سانس‌ها با JS اضافه می‌شوند -->
        </div>
    </div>

    <!-- Step 4: Contact Details & Submit -->
    <div id="step-4" class="wizard-step hidden">
        <h3 class="text-lg font-bold text-gray-800 mb-4">تکمیل اطلاعات تماس:</h3>
        <form id="booking-final-form" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">نام و نام خانوادگی</label>
                <input type="text" id="cust-name" required class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="مثال: علی محمدی">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">شماره همراه</label>
                <input type="tel" id="cust-phone" required class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="09123456789">
            </div>
            <button type="submit" id="submit-booking-btn" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-emerald-100">
                تأیید و ثبت نهایی رزرو
            </button>
        </form>
    </div>

    <!-- Success Message -->
    <div id="step-success" class="wizard-step hidden text-center py-8">
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">✓</div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">رزرو شما با موفقیت ثبت شد!</h3>
        <p class="text-gray-500 text-sm">اطلاعات جلسه برای شما و مدرس ارسال گردید.</p>
    </div>
</div>