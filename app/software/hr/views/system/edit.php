<?php
/**
 * HR Analyzer - فرم ویرایش سیستم شرکت
 * مسیر: app/software/hr/views/system/edit.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-building"></i> اطلاعات شرکت
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                اطلاعات شرکت خود را مدیریت کنید.
            </p>
        </div>
        <a href="<?= hr_url('dashboard') ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت به داشبورد
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('system', 'update') ?>">
        <?= $this->csrfField() ?>

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
                               value="<?= hr_e($system['company_name'] ?? '') ?>"
                               required maxlength="200">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="industry">صنعت</label>
                        <?php
                        $industries = [
                            'general'        => 'عمومی',
                            'manufacturing'  => 'تولیدی',
                            'engineering'    => 'مهندسی',
                            'oil_gas'        => 'نفت و گاز',
                            'petrochemical'  => 'پتروشیمی',
                            'power'          => 'نیروگاه',
                            'food'           => 'صنایع غذایی',
                            'pharmaceutical' => 'داروسازی',
                            'mining'         => 'معدن',
                            'cement'         => 'سیمان',
                            'steel'          => 'فولاد',
                            'automotive'     => 'خودروسازی',
                            'it'             => 'فناوری اطلاعات',
                            'consulting'     => 'مشاوره',
                            'other'          => 'سایر',
                        ];
                        $currentIndustry = $system['industry'] ?? 'general';
                        ?>
                        <select id="industry" name="industry" class="hr-form-control">
                            <?php foreach ($industries as $key => $label): ?>
                                <option value="<?= hr_e($key) ?>"
                                    <?= $currentIndustry === $key ? 'selected' : '' ?>>
                                    <?= hr_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="company_size">اندازه شرکت</label>
                        <?php
                        $sizes = [
                            'micro'      => 'خرد (۱-۹ نفر)',
                            'small'      => 'کوچک (۱۰-۴۹ نفر)',
                            'medium'     => 'متوسط (۵۰-۲۴۹ نفر)',
                            'large'      => 'بزرگ (۲۵۰-۹۹۹ نفر)',
                            'enterprise' => 'سازمانی (۱۰۰۰+ نفر)',
                        ];
                        $currentSize = $system['company_size'] ?? 'medium';
                        ?>
                        <select id="company_size" name="company_size" class="hr-form-control">
                            <?php foreach ($sizes as $key => $label): ?>
                                <option value="<?= hr_e($key) ?>"
                                    <?= $currentSize === $key ? 'selected' : '' ?>>
                                    <?= hr_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="fiscal_year_start">شروع سال مالی</label>
                        <input type="text" id="fiscal_year_start" name="fiscal_year_start"
                               class="hr-form-control"
                               value="<?= hr_e($system['fiscal_year_start'] ?? '01/01') ?>"
                               maxlength="5">
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="description">توضیحات</label>
                    <textarea id="description" name="description"
                              class="hr-form-control" rows="3"
                              maxlength="1000"><?= hr_e($system['description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

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
                               value="<?= hr_e($system['phone'] ?? '') ?>"
                               maxlength="50">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="email">ایمیل</label>
                        <input type="email" id="email" name="email"
                               class="hr-form-control"
                               value="<?= hr_e($system['email'] ?? '') ?>"
                               maxlength="150">
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="website">وب‌سایت</label>
                    <input type="url" id="website" name="website"
                           class="hr-form-control"
                           value="<?= hr_e($system['website'] ?? '') ?>"
                           maxlength="255">
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="address">آدرس</label>
                    <textarea id="address" name="address"
                              class="hr-form-control" rows="2"
                              maxlength="500"><?= hr_e($system['address'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- اطلاعات سیستمی -->
        <div class="card hr-mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i>
                    اطلاعات سیستمی
                </h3>
            </div>
            <div class="card-body">
                <table class="hr-table">
                    <tbody>
                        <tr>
                            <th style="width: 200px;">شناسه سیستم</th>
                            <td><?= hr_num($system['id'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th>تعداد پرسنل (cache)</th>
                            <td><?= hr_num($system['employee_count'] ?? 0) ?> نفر</td>
                        </tr>
                        <tr>
                            <th>تاریخ ایجاد</th>
                            <td><?= hr_date($system['created_at'] ?? null, 'Y/m/d H:i') ?></td>
                        </tr>
                        <tr>
                            <th>آخرین به‌روزرسانی</th>
                            <td><?= hr_date($system['updated_at'] ?? null, 'Y/m/d H:i') ?></td>
                        </tr>
                        <tr>
                            <th>وضعیت</th>
                            <td>
                                <?php if (($system['status'] ?? '') === 'active'): ?>
                                    <span class="hr-status-badge hr-status-active">فعال</span>
                                <?php else: ?>
                                    <span class="hr-status-badge hr-status-inactive">غیرفعال</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary">
                <i class="fas fa-save"></i> ذخیره تغییرات
            </button>
            <a href="<?= hr_url('dashboard') ?>" class="btn-hr-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr.js"></script>