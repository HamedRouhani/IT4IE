<?php
$value = static fn(string $key, $default = '') => ee_e($old[$key] ?? $default);
$isSelected = static fn(string $key, string $candidate) => (string) ($old[$key] ?? '') === $candidate ? 'selected' : '';
$kinds = ['investment' => 'سرمایه‌گذاری', 'replacement' => 'جایگزینی دارایی', 'make_buy' => 'ساخت یا خرید', 'public' => 'پروژهٔ عمومی', 'cost_only' => 'فقط هزینه'];
$depreciations = ['none' => 'بدون استهلاک', 'straight' => 'خط مستقیم', 'declining' => 'نزولی', 'soyd' => 'مجموع سنوات', 'uop' => 'واحد تولید'];
$currencies = ['IRT' => 'IRT — تومان', 'IRR' => 'IRR — ریال', 'USD' => 'USD — دلار', 'EUR' => 'EUR — یورو'];
?>
<section class="ee-page" dir="rtl">
    <header class="ee-page-head">
        <div>
            <span class="ee-eyebrow">تعریف مطالعه</span>
            <h1><i class="fa-solid fa-file-invoice-dollar"></i> پروژهٔ اقتصادی جدید</h1>
            <p>فرض‌های کلان را یک‌بار ثبت کنید؛ همهٔ تحلیل‌ها و گزارش‌ها بر همین مبنا محاسبه می‌شوند.</p>
        </div>
        <div class="ee-head-actions">
            <a class="ee-button" href="<?= ee_e(ee_url('project', 'index')) ?>"><i class="fa-solid fa-arrow-right"></i> بازگشت</a>
        </div>
    </header>

    <?php if (!empty($flash)): ?>
        <div class="ee-alert <?= ee_e($flash['type']) ?>" role="status"><?= ee_e($flash['message']) ?></div>
    <?php endif; ?>

    <form class="ee-form ee-card" method="post" action="<?= ee_e(ee_url('project', 'store')) ?>">
        <?= csrf_field() ?>

        <div class="ee-form-section">
            <h3>۱. مشخصات و دامنه</h3>
            <div class="ee-form-grid">
                <label>عنوان پروژه *<input name="title" maxlength="180" required value="<?= $value('title') ?>" placeholder="مثلاً جایگزینی خط بسته‌بندی"></label>
                <label>نوع مطالعه
                    <select name="study_kind">
                        <?php foreach ($kinds as $key => $label): ?>
                            <option value="<?= ee_e($key) ?>" <?= $isSelected('study_kind', $key) ?>><?= ee_e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>ارز
                    <select name="currency">
                        <?php foreach ($currencies as $code => $label): ?>
                            <option value="<?= ee_e($code) ?>" <?= $isSelected('currency', $code) ?>><?= ee_e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small>همهٔ مبالغ در همین ارز ثبت و گزارش می‌شوند.</small>
                </label>
                <label>سال پایه (شمسی، اختیاری)<input name="base_year" inputmode="numeric" maxlength="4" value="<?= $value('base_year') ?>" placeholder="۱۴۰۵"></label>
                <label class="ee-span-2">توضیح کوتاه<textarea name="description" rows="3" maxlength="5000" placeholder="دامنه، محدودیت‌ها و هدف مطالعه"><?= $value('description') ?></textarea></label>
            </div>
        </div>

        <div class="ee-form-section">
            <h3>۲. نرخ‌ها، مالیات و استهلاک</h3>
            <div class="ee-form-grid">
                <label>دورهٔ مطالعه (سال) *
                    <input type="number" name="study_period" min="1" max="100" required value="<?= $value('study_period', '5') ?>">
                    <small>افق تحلیل؛ برای تحلیل جایگزینی، عمر گزینه‌ها را جداگانه ثبت می‌کنید.</small>
                </label>
                <label>نرخ بازدهٔ حداقلی (MARR ٪) *
                    <input type="number" step="0.001" name="marr_pct" min="-99.999" max="100" required value="<?= $value('marr_pct', '12') ?>">
                    <small>هزینهٔ سرمایهٔ وزنی، نرخ فرصت یا سیاست مصوب سازمان.</small>
                </label>
                <label>تورم سالانه (٪)
                    <input type="number" step="0.001" name="inflation_pct" min="-99.999" max="100" value="<?= $value('inflation_pct', '0') ?>">
                    <small>برای تبدیل نرخ اسمی و حقیقی؛ صفر یعنی تحلیل بدون تورم.</small>
                </label>
                <label>نرخ مالیات (٪)
                    <input type="number" step="0.001" name="tax_rate_pct" min="0" max="60" value="<?= $value('tax_rate_pct', '0') ?>">
                    <small>سپر مالیاتی استهلاک در جریان نقدی پس از مالیات لحاظ می‌شود.</small>
                </label>
                <label class="ee-span-2">روش استهلاک
                    <select name="depreciation_method">
                        <?php foreach ($depreciations as $key => $label): ?>
                            <option value="<?= ee_e($key) ?>" <?= $isSelected('depreciation_method', $key) ?>><?= ee_e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small>روش باید با رویهٔ حسابداری و مقررات مالیاتی سازمان هم‌خوان باشد.</small>
                </label>
            </div>
        </div>

        <div class="ee-form-section">
            <h3>۳. مستندسازی</h3>
            <div class="ee-form-grid">
                <label class="ee-span-2">یادداشت<textarea name="notes" rows="3" maxlength="5000" placeholder="فرض‌ها، منبع نرخ‌ها و ملاحظات تصمیم"><?= $value('notes') ?></textarea></label>
            </div>
            <div class="ee-note">
                <i class="fa-solid fa-circle-info"></i>
                <div>
                    <strong>چرا ثبت این فرض‌ها الزامی است؟</strong>
                    <ul>
                        <li>MARR پایهٔ مقایسه با «هزینهٔ فرصت» است؛ عدد بدون منبع، تصمیم را غیرقابل دفاع می‌کند.</li>
                        <li>نرخ تورم تعیین می‌کند تحلیل با پول جاری انجام شود یا پول ثابت.</li>
                        <li>نرخ مالیات و روش استهلاک، جریان نقدی پس از مالیات را تغییر می‌دهند.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="ee-form-actions">
            <button class="ee-button ee-button-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> ذخیرهٔ پروژه</button>
            <a class="ee-button ee-button-ghost" href="<?= ee_e(ee_url('project', 'index')) ?>">انصراف</a>
        </div>
    </form>
</section>
