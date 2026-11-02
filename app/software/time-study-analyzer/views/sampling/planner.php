<?php if ($active): ?>
<section class="ts-card ts-no-print">
    <div class="ts-card-head"><div><span class="ts-eyebrow">طراحی برنامهٔ میدانی</span><h2>ساخت زمان‌های تصادفی</h2><p>زمان‌ها را پیش از شیفت تولید و در اختیار مشاهده‌گر قرار دهید. زمان‌های تکراری با برنامه‌های قبلی دوباره ثبت نمی‌شوند.</p></div><span class="ts-status active">زمان محلی: تهران</span></div>
    <form method="post" action="<?= ts_e(ts_url('study', 'generateSchedule', ['id' => $study['id']])) ?>" class="ts-form-grid ts-sampling-planner" data-ts-form>
        <?= $csrfHtml ?>
        <label>تاریخ شروع شیفت (شمسی)<input class="hr-datepicker" name="sample_date" required inputmode="numeric" autocomplete="off" placeholder="۱۴۰۵/۰۸/۱۰"></label>
        <label>ساعت شروع<input type="time" name="shift_start" required></label>
        <label>ساعت پایان<input type="time" name="shift_end" required><small>اگر پایان از نظر ساعت زودتر از شروع باشد، شیفت تا روز بعد ادامه دارد.</small></label>
        <label>تعداد زمان‌های جدید<input type="number" name="sample_count" min="1" max="500" required value="30"><small>در هر نوبت حداکثر ۵۰۰ زمان؛ در چند روز برنامه‌های جدا بسازید.</small></label>
        <div class="ts-form-actions ts-span-2"><button class="ts-button ts-button-primary" type="submit"><i class="fa-solid fa-shuffle"></i> تولید و افزودن به برنامه</button></div>
    </form>
</section>
<?php endif; ?>
