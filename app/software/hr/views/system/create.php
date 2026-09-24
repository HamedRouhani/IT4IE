<?php
/**
 * HR Analyzer - فرم ایجاد سیستم شرکت
 * مسیر: app/software/hr/views/system/create.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-mb-4">
        <h2 style="color: var(--hr-primary-dark); margin: 0;">
            <i class="fas fa-building"></i> ایجاد سیستم منابع انسانی
        </h2>
        <p class="hr-text-muted hr-mt-2">
            برای شروع کار با ماژول HR، ابتدا اطلاعات شرکت خود را وارد کنید.
        </p>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('system', 'store') ?>">
        <?= $this->csrfField() ?>

        <!-- اطلاعات پایه -->
        <div class="card hr-mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i>
                    اطلاعات پایه
                </h3>
            </div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="company_name">
                            نام شرکت <span style="color: var(--hr-danger);">*</span>
                        </label>
                        <input type="text" id="company_name" name="company_name"
                               class="hr-form-control"
                               placeholder="مثال: شرکت مهندسی نمونه"
                               required maxlength="200">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="industry">صنعت</label>
                        <select id="industry" name="industry" class="hr-form-control">
                            <option value="general">عمومی</option>
                            <option value="manufacturing">تولیدی</option>
                            <option value="engineering">مهندسی</option>
                            <option value="oil_gas">نفت و گاز</option>
                            <option value="petrochemical">پتروشیمی</option>
                            <option value="power">نیروگاه</option>
                            <option value="food">صنایع غذایی</option>
                            <option value="pharmaceutical">داروسازی</option>
                            <option value="mining">معدن</option>
                            <option value="cement">سیمان</option>
                            <option value="steel">فولاد</option>
                            <option value="automotive">خودروسازی</option>
                            <option value="it">فناوری اطلاعات</option>
                            <option value="consulting">مشاوره</option>
                            <option value="other">سایر</option>
                        </select>
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="company_size">اندازه شرکت</label>
                        <select id="company_size" name="company_size" class="hr-form-control">
                            <option value="micro">خرد (۱-۹ نفر)</option>
                            <option value="small">کوچک (۱۰-۴۹ نفر)</option>
                            <option value="medium" selected>متوسط (۵۰-۲۴۹ نفر)</option>
                            <option value="large">بزرگ (۲۵۰-۹۹۹ نفر)</option>
                            <option value="enterprise">سازمانی (۱۰۰۰+ نفر)</option>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="fiscal_year_start">شروع سال مالی</label>
                        <input type="text" id="fiscal_year_start" name="fiscal_year_start"
                               class="hr-form-control"
                               value="01/01"
                               placeholder="MM/DD"
                               maxlength="5">
                        <small class="hr-text-muted">فرمت: MM/DD (ماه/روز) — مثلاً 01/01 برای اول فروردین</small>
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="description">توضیحات</label>
                    <textarea id="description" name="description"
                              class="hr-form-control" rows="3"
                              maxlength="1000"
                              placeholder="توضیحات اختیاری درباره شرکت..."></textarea>
                </div>
            </div>
        </div>

        <!-- اطلاعات تماس -->
        <div class="card hr-mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-address-card"></i>
                    اطلاعات تماس
                </h3>
            </div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="phone">تلفن</label>
                        <input type="text" id="phone" name="phone"
                               class="hr-form-control"
                               placeholder="021-12345678"
                               maxlength="50">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="email">ایمیل</label>
                        <input type="email" id="email" name="email"
                               class="hr-form-control"
                               placeholder="info@example.com"
                               maxlength="150">
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="website">وب‌سایت</label>
                    <input type="url" id="website" name="website"
                           class="hr-form-control"
                           placeholder="https://example.com"
                           maxlength="255">
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="address">آدرس</label>
                    <textarea id="address" name="address"
                              class="hr-form-control" rows="2"
                              maxlength="500"></textarea>
                </div>
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary">
                <i class="fas fa-save"></i> ذخیره و شروع
            </button>
            <a href="<?= hr_url('dashboard') ?>" class="btn-hr-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr.js"></script>