<?php
$source = array_merge(is_array($project) ? $project : [], is_array($old) ? $old : []);
$value = static fn(string $key, $default = '') => fl_e($source[$key] ?? $default);
$selected = static fn(string $key, string $candidate, string $default = '') => (string) ($source[$key] ?? $default) === $candidate ? 'selected' : '';
?>
<main class="fl-page" dir="rtl">
    <header class="fl-page-head"><div><span class="fl-eyebrow">گام ۱ · مشخصات مسئله و زمان</span><h1><i class="fa-solid fa-industry"></i> <?= $project ? 'ویرایش پروژهٔ چیدمان' : 'تعریف پروژهٔ چیدمان' ?></h1><p>دورهٔ برنامه‌ریزی، مبنای زمانی ثبت مقدار تولید در گام محصول خواهد بود؛ تقویم کاری نیز برای محاسبات ظرفیت زمانی نگهداری می‌شود.</p></div><a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('project', 'index')) ?>"><i class="fa-solid fa-arrow-right"></i> بازگشت به پروژه‌ها</a></header>
    <?php if (!empty($flash)): ?><div class="fl-alert <?= fl_e($flash['type']) ?>" role="status"><?= fl_e($flash['message']) ?></div><?php endif; ?>

    <form class="fl-card fl-form" method="post" action="<?= fl_e(fl_url('project', $project ? 'update' : 'store', $project ? ['id' => (int) $project['id']] : [])) ?>">
        <?= $csrfField ?>
        <section class="fl-form-section"><span class="fl-eyebrow">شناخت مسئله</span><h2>مشخصات کارخانه و دامنه</h2><div class="fl-form-grid">
            <label>عنوان پروژه *<input name="title" maxlength="180" required value="<?= $value('title') ?>" placeholder="مثلاً طراحی مجدد چیدمان سالن مونتاژ"></label>
            <label>نام کارخانه یا سایت<input name="facility_name" maxlength="180" value="<?= $value('facility_name') ?>" placeholder="نام مجموعه یا محل تولید"></label>
            <label>صنعت<input name="industry" maxlength="120" value="<?= $value('industry') ?>" placeholder="مثلاً صنایع غذایی"></label>
            <label>موقعیت سایت<input name="site_location" maxlength="180" value="<?= $value('site_location') ?>" placeholder="شهر، شهرک صنعتی یا نشانی داخلی"></label>
            <label class="fl-span-2">دامنه و هدف مطالعه<textarea name="scope_description" rows="4" maxlength="5000" placeholder="واحدها یا خطوط داخل دامنه، مسئلهٔ فعلی و هدف بهبود را توضیح دهید."><?= $value('scope_description') ?></textarea><small>در این مرحله فقط دامنه ثبت می‌شود؛ هیچ فرضی دربارهٔ ظرفیت یا ابعاد ساختمان ساخته نمی‌شود.</small></label>
        </div></section>

        <?php if ($project): ?><section class="fl-form-section"><span class="fl-eyebrow">مدیریت پروژه</span><h2>وضعیت پروژه</h2><div class="fl-form-grid"><label>وضعیت فعلی<select name="status" required><?php foreach ($statuses as $key => $label): ?><option value="<?= fl_e($key) ?>" <?= $selected('status', $key, 'draft') ?>><?= fl_e($label) ?></option><?php endforeach; ?></select><small>وضعیت را متناسب با پیشرفت مطالعه به‌روز کنید.</small></label></div></section><?php endif; ?>
        <section class="fl-form-section"><span class="fl-eyebrow">گام T · زمان‌بندی</span><h2>مبنای تولید و تقویم کاری</h2><div class="fl-form-grid">
            <label>دورهٔ مبنای مقدار تولید *<select name="planning_period" required><?php foreach ($periods as $key => $label): ?><option value="<?= fl_e($key) ?>" <?= $selected('planning_period', $key, 'month') ?>><?= fl_e($label) ?></option><?php endforeach; ?></select><small>مقدار تولید هر محصول در گام بعد با همین دوره ثبت می‌شود.</small></label>
            <label>تعداد شیفت در روز *<input type="number" name="shifts_per_day" min="1" max="4" step="1" required value="<?= fl_e(fl_latin_digits((string) ($source['shifts_per_day'] ?? '1'))) ?>"><small>عدد صحیح از ۱ تا ۴.</small></label>
            <label>ساعت برنامه‌ریزی‌شده در هر شیفت *<input type="text" name="hours_per_shift" inputmode="decimal" pattern="[0-9۰-۹٠-٩]+([٫.][0-9۰-۹٠-٩]{1,2})?" required value="<?= fl_e(fl_latin_digits((string) ($source['hours_per_shift'] ?? '8.00'))) ?>" placeholder="مثلاً ۸ یا ۷٫۵"><small>بین ۰٫۲۵ تا ۲۴ ساعت؛ زمان استراحت را وارد نکنید.</small></label>
            <label>روز کاری در هفته *<input type="number" name="working_days_per_week" min="1" max="7" step="1" required value="<?= fl_e(fl_latin_digits((string) ($source['working_days_per_week'] ?? '5'))) ?>"><small>عدد صحیح از ۱ تا ۷.</small></label>
        </div><aside class="fl-inline-note"><i class="fa-solid fa-circle-info"></i><span>این تقویم، برنامهٔ اسمی هفتگی را توصیف می‌کند؛ تعطیلات، توقفات، استراحت، اضافه‌کاری و زمان قابل‌دسترس تجهیزات در مرحلهٔ ظرفیت و تحلیل فضا جداگانه بررسی می‌شوند.</span></aside></section>

        <div class="fl-form-actions"><button class="fl-button fl-button-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> <?= $project ? 'ذخیرهٔ تغییرات' : 'ذخیره و ادامه' ?></button><a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('project', 'index')) ?>">انصراف</a></div>
    </form>
</main>
