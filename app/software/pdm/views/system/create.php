<?php
/**
 * PdM Analyzer - فرم ایجاد سیستم شرکت
 * مسیر: app/software/pdm/views/system/create.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <!-- هدر -->
    <div class="pdm-mb-4">
        <h2 style="color: var(--pdm-primary-dark); margin: 0;">
            <i class="fas fa-building"></i> ایجاد سیستم شرکت
        </h2>
        <p class="pdm-text-muted pdm-mt-2">
            برای شروع کار با ماژول PdM، ابتدا اطلاعات شرکت خود را وارد کنید.
        </p>
    </div>

    <!-- پیام Flash -->
    <?php if (!empty($flash)): ?>
        <div class="pdm-alert <?= pdm_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= pdm_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- فرم -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle"></i>
                اطلاعات شرکت
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= pdm_url('system', 'store') ?>">
                <?= $this->csrfField() ?>

                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="company_name">
                            نام شرکت <span style="color: var(--pdm-danger);">*</span>
                        </label>
                        <input type="text"
                               id="company_name"
                               name="company_name"
                               class="pdm-form-control"
                               placeholder="مثال: شرکت تولیدی نمونه"
                               required
                               maxlength="150">
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="industry">صنعت</label>
                        <select id="industry" name="industry" class="pdm-form-control">
                            <option value="general">عمومی</option>
                            <option value="manufacturing">تولیدی</option>
                            <option value="oil_gas">نفت و گاز</option>
                            <option value="petrochemical">پتروشیمی</option>
                            <option value="power">نیروگاه</option>
                            <option value="food">صنایع غذایی</option>
                            <option value="pharmaceutical">داروسازی</option>
                            <option value="mining">معدن</option>
                            <option value="cement">سیمان</option>
                            <option value="steel">فولاد</option>
                            <option value="automotive">خودروسازی</option>
                            <option value="other">سایر</option>
                        </select>
                    </div>
                </div>

                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="description">توضیحات</label>
                    <textarea id="description"
                              name="description"
                              class="pdm-form-control"
                              rows="4"
                              placeholder="توضیحات اختیاری درباره شرکت..."
                              maxlength="1000"></textarea>
                </div>

                <div class="pdm-flex pdm-gap-2 pdm-mt-3">
                    <button type="submit" class="btn-pdm-primary">
                        <i class="fas fa-save"></i> ذخیره و شروع
                    </button>
                    <a href="<?= pdm_url('dashboard') ?>" class="btn-pdm-outline">
                        <i class="fas fa-times"></i> انصراف
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/pdm.js"></script>