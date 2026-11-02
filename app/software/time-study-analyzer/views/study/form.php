<?php
$editing = !empty($study);
$method = $method ?? ($study['method'] ?? 'stopwatch');
$value = static fn(string $key, $default = '') => ts_e($study[$key] ?? $default);
$tehranToday = (new DateTimeImmutable('now', new DateTimeZone('Asia/Tehran')))->format('Y-m-d');
$dateValue = $study ? ts_date($study['study_date']) : ts_date($tehranToday);
$elements = $study['elements'] ?? [['name'=>'','code'=>'','element_type'=>'manual','frequency_per_cycle'=>'1','notes'=>'']];
$categories = $study['categories'] ?? [['name'=>'','category_type'=>'productive'], ['name'=>'','category_type'=>'delay']];
$methodLabel = $method === 'stopwatch' ? 'زمان‌سنجی مستقیم' : 'نمونه‌برداری کار';
?>
<section class="ts-page" dir="rtl">
    <header class="ts-page-head"><div><span class="ts-eyebrow"><?= $editing ? 'پیش‌نویس قابل ویرایش' : 'تعریف روش پیش از مشاهده' ?></span><h1><i class="fa-solid <?= $method === 'stopwatch' ? 'fa-stopwatch' : 'fa-chart-pie' ?>"></i> <?= $editing ? 'ویرایش مطالعه' : 'تعریف مطالعهٔ ' . ts_e($methodLabel) ?></h1><p>روش انجام کار، دامنهٔ مطالعه و مبنای تحلیل را پیش از آغاز ثبت کنید.</p></div><a class="ts-button ts-button-outline" href="<?= ts_e($editing ? ts_url('study', 'show', ['id'=>$study['id']]) : ts_url('study')) ?>"><i class="fa-solid fa-arrow-right"></i> بازگشت</a></header>
    <?php if (!empty($flash)): ?><div class="ts-alert <?= ts_e($flash['type']) ?>" role="status"><?= ts_e($flash['message']) ?></div><?php endif; ?>
    <form class="ts-card ts-form" method="post" action="<?= ts_e($editing ? ts_url('study', 'update', ['id'=>$study['id']]) : ts_url('study', 'store')) ?>" data-ts-form>
        <?= $csrfHtml ?><input type="hidden" name="method" value="<?= ts_e($method) ?>">
        <div class="ts-form-section"><div class="ts-form-section-head"><span>۱</span><div><h2>مشخصات و دامنه</h2><p>شناسهٔ مشاهده‌شونده اختیاری است؛ برای تحلیل زمان از اطلاعات شخصی غیرضروری استفاده نکنید.</p></div></div>
            <div class="ts-form-grid">
                <label>عنوان مطالعه *<input name="title" maxlength="180" required value="<?= $value('title') ?>" placeholder="مثلاً مونتاژ مجموعهٔ پمپ"></label>
                <label>نام عملیات *<input name="process_name" maxlength="180" required value="<?= $value('process_name') ?>" placeholder="مثلاً بستن درپوش"></label>
                <label>تاریخ مطالعه (شمسی) *<input class="hr-datepicker" name="study_date" required inputmode="numeric" autocomplete="off" value="<?= ts_e($dateValue) ?>" placeholder="۱۴۰۵/۰۸/۱۰"></label>
                <label>محصول یا خدمت<input name="product_name" maxlength="180" value="<?= $value('product_name') ?>"></label>
                <label>واحد سازمانی<input name="department" maxlength="120" value="<?= $value('department') ?>"></label>
                <label>ایستگاه / محل کار<input name="work_center" maxlength="120" value="<?= $value('work_center') ?>"></label>
                <label>شیفت<input name="shift_label" maxlength="80" value="<?= $value('shift_label') ?>" placeholder="صبح، عصر، شب"></label>
                <label>واحد خروجی *<input name="unit_name" maxlength="60" required value="<?= $value('unit_name', 'قطعه') ?>" placeholder="قطعه، سفارش، پرونده"></label>
                <label>شناسهٔ مشاهده‌شونده (اختیاری)<input name="operator_ref" maxlength="120" value="<?= $value('operator_ref') ?>"></label>
                <label class="ts-span-2">روش فعلی انجام کار *<textarea name="method_description" rows="4" maxlength="5000" required placeholder="توالی کار، ابزار، مواد، نقطهٔ شروع و پایان هر چرخه را دقیق بنویسید."><?= $value('method_description') ?></textarea></label>
                <label class="ts-span-2">شرایط کار و نکات ارگونومی<textarea name="work_conditions" rows="3" maxlength="5000" placeholder="چیدمان، وضعیت بدنی، ابزار، شرایط محیطی، خطرها و مشارکت کارکنان را ثبت کنید."><?= $value('work_conditions') ?></textarea></label>
            </div>
        </div>

        <?php if ($method === 'stopwatch'): ?>
        <div class="ts-form-section"><div class="ts-form-section-head"><span>۲</span><div><h2>عناصر کار</h2><p>مرز شروع و پایان عناصر باید روشن باشد. عناصر دستی از زمان ماشین جدا شوند تا rating فقط بر زمان تحت کنترل انسان اعمال شود.</p></div></div>
            <div class="ts-repeat-list" data-ts-repeat="elements">
            <?php foreach ($elements as $element): ?><div class="ts-repeat-row" data-ts-row>
                <label>نام عنصر *<input name="element_name[]" maxlength="180" value="<?= ts_e($element['name'] ?? '') ?>" placeholder="گرفتن قطعه"></label>
                <label>کد<input name="element_code[]" maxlength="32" value="<?= ts_e($element['element_code'] ?? $element['code'] ?? '') ?>"></label>
                <label>نوع عنصر<select name="element_type[]"><option value="manual" <?= ($element['element_type'] ?? $element['type'] ?? 'manual') === 'manual' ? 'selected' : '' ?>>دستی</option><option value="machine" <?= ($element['element_type'] ?? $element['type'] ?? '') === 'machine' ? 'selected' : '' ?>>ماشین</option></select></label>
                <label>تکرار در هر واحد<input name="element_frequency[]" type="number" min="0.0001" max="10000" step="0.0001" required value="<?= ts_e($element['frequency_per_cycle'] ?? $element['frequency'] ?? '1') ?>"></label>
                <label>یادداشت<input name="element_note[]" maxlength="500" value="<?= ts_e($element['notes'] ?? '') ?>"></label>
                <button type="button" class="ts-icon-button" data-ts-remove aria-label="حذف عنصر"><i class="fa-solid fa-trash"></i></button>
            </div><?php endforeach; ?>
            </div><button type="button" class="ts-button ts-button-outline" data-ts-add="elements"><i class="fa-solid fa-plus"></i> افزودن عنصر کار</button>
        </div>
        <div class="ts-form-section"><div class="ts-form-section-head"><span>۳</span><div><h2>مبنای نرمال‌سازی</h2><p>ارزیابی عملکرد برای عناصر دستی ثبت می‌شود؛ عنصر ماشین با rating برابر ۱۰۰٪ محاسبه می‌شود.</p></div></div>
        <?php else: ?>
        <div class="ts-form-section"><div class="ts-form-section-head"><span>۲</span><div><h2>دسته‌های فعالیت</h2><p>دسته‌ها را طوری تعریف کنید که در زمان مشاهده، وضعیت کارگر یا ماشین بدون هم‌پوشانی قابل طبقه‌بندی باشد.</p></div></div>
            <div class="ts-repeat-list" data-ts-repeat="categories">
            <?php foreach ($categories as $category): ?><div class="ts-repeat-row ts-repeat-row--category" data-ts-row>
                <label>نام دسته *<input name="category_name[]" maxlength="120" value="<?= ts_e($category['name'] ?? '') ?>" placeholder="کار مستقیم"></label>
                <label>طبقه<select name="category_type[]"><?php foreach (['productive'=>'مولد','machine'=>'کار ماشین','personal'=>'نیاز شخصی','delay'=>'توقف / انتظار','other'=>'سایر'] as $key=>$label): ?><option value="<?= ts_e($key) ?>" <?= ($category['category_type'] ?? 'productive') === $key ? 'selected' : '' ?>><?= ts_e($label) ?></option><?php endforeach; ?></select></label>
                <button type="button" class="ts-icon-button" data-ts-remove aria-label="حذف دسته"><i class="fa-solid fa-trash"></i></button>
            </div><?php endforeach; ?>
            </div><button type="button" class="ts-button ts-button-outline" data-ts-add="categories"><i class="fa-solid fa-plus"></i> افزودن دسته</button>
        </div>
        <div class="ts-form-section"><div class="ts-form-section-head"><span>۳</span><div><h2>دقت و سطح اطمینان</h2><p>برای حجم نمونه، دقت مطلق را به‌صورت درصد-نقطه تعریف کنید؛ مقدار پیش‌فرض برنامه ۵٪ است و مقدار نهایی بر اساس بدترین حالت p=0.5 پیشنهاد می‌شود.</p></div></div>
        <?php endif; ?>
            <div class="ts-form-grid">
                <label>سطح اطمینان *<select name="confidence_level"><option value="90" <?= $value('confidence_level', '95') === '90' ? 'selected' : '' ?>>۹۰٪</option><option value="95" <?= $value('confidence_level', '95') === '95' ? 'selected' : '' ?>>۹۵٪</option><option value="99" <?= $value('confidence_level', '95') === '99' ? 'selected' : '' ?>>۹۹٪</option></select></label>
                <label>دقت هدف (%) *<input name="precision_pct" type="number" min="0.1" max="<?= $method === 'stopwatch' ? '50' : '30' ?>" step="0.1" required value="<?= $value('precision_pct', '5.00') ?>"><small><?= $method === 'stopwatch' ? 'نسبت به میانگین زمان نرمال چرخه' : 'درصد-نقطه برای نسبت‌های فعالیت' ?></small></label>
                <?php if ($method === 'stopwatch'): ?>
                <label>مبنای allowance<select name="allowance_basis"><option value="standard" <?= $value('allowance_basis', 'standard') === 'standard' ? 'selected' : '' ?>>درصدی از زمان استاندارد: NT ÷ (1 − A)</option><option value="normal" <?= $value('allowance_basis', 'standard') === 'normal' ? 'selected' : '' ?>>درصد افزوده بر زمان نرمال: NT × (1 + A)</option></select></label>
                <label>نیاز شخصی (%)<input name="personal_allowance_pct" type="number" min="0" max="79.99" step="0.01" value="<?= $value('personal_allowance_pct', '0') ?>"></label>
                <label>خستگی (%)<input name="fatigue_allowance_pct" type="number" min="0" max="79.99" step="0.01" value="<?= $value('fatigue_allowance_pct', '0') ?>"></label>
                <label>تأخیر اجتناب‌ناپذیر (%)<input name="delay_allowance_pct" type="number" min="0" max="79.99" step="0.01" value="<?= $value('delay_allowance_pct', '0') ?>"></label>
                <label>سایر allowance (%)<input name="other_allowance_pct" type="number" min="0" max="79.99" step="0.01" value="<?= $value('other_allowance_pct', '0') ?>"></label>
                <label class="ts-span-2">منبع / توجیه allowance<textarea name="allowance_rationale" rows="2" maxlength="1000" placeholder="منبع سازمانی، توافق کارفرما و کارکنان یا مبنای ارگونومی"><?= $value('allowance_rationale') ?></textarea></label>
                <?php else: ?><input type="hidden" name="allowance_basis" value="standard"><?php endif; ?>
            </div>
            <?php if ($method === 'stopwatch'): ?><div class="ts-note ts-note-compact"><i class="fa-solid fa-circle-info"></i><div><strong>تعریف فرمول را ثابت نگه دارید</strong><p>مجموع allowance باید کمتر از ۸۰٪ باشد و فقط بر زمان دستی اثر می‌گذارد؛ زمان ماشین جداگانه افزوده می‌شود. درصدها را از یک سیاست مصوب و متناسب با شرایط کار بگیرید، نه از یک مقدار جهان‌شمول.</p></div></div><?php endif; ?>
        </div>

        <div class="ts-form-actions"><button class="ts-button ts-button-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> <?= $editing ? 'ذخیرهٔ پیش‌نویس' : 'ساخت پیش‌نویس' ?></button><a class="ts-button ts-button-outline" href="<?= ts_e($editing ? ts_url('study', 'show', ['id'=>$study['id']]) : ts_url('study')) ?>">انصراف</a></div>
    </form>
</section>
