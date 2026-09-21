<?php
/**
 * PdM Analyzer - فرم ویرایش سیستم شرکت
 * مسیر: app/software/pdm/views/system/edit.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <!-- هدر -->
    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-building"></i> اطلاعات شرکت
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                اطلاعات شرکت خود را ویرایش کنید.
            </p>
        </div>
        <a href="<?= pdm_url('dashboard') ?>" class="btn-pdm-outline">
            <i class="fas fa-arrow-right"></i> بازگشت به داشبورد
        </a>
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
                <i class="fas fa-edit"></i>
                ویرایش اطلاعات
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= pdm_url('system', 'update') ?>">
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
                               value="<?= pdm_e($system['company_name'] ?? '') ?>"
                               required
                               maxlength="150">
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="industry">صنعت</label>
                        <?php
                        $industries = [
                            'general'        => 'عمومی',
                            'manufacturing'  => 'تولیدی',
                            'oil_gas'        => 'نفت و گاز',
                            'petrochemical'  => 'پتروشیمی',
                            'power'          => 'نیروگاه',
                            'food'           => 'صنایع غذایی',
                            'pharmaceutical' => 'داروسازی',
                            'mining'         => 'معدن',
                            'cement'         => 'سیمان',
                            'steel'          => 'فولاد',
                            'automotive'     => 'خودروسازی',
                            'other'          => 'سایر',
                        ];
                        $currentIndustry = $system['industry'] ?? 'general';
                        ?>
                        <select id="industry" name="industry" class="pdm-form-control">
                            <?php foreach ($industries as $key => $label): ?>
                                <option value="<?= pdm_e($key) ?>"
                                    <?= $currentIndustry === $key ? 'selected' : '' ?>>
                                    <?= pdm_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="description">توضیحات</label>
                    <textarea id="description"
                              name="description"
                              class="pdm-form-control"
                              rows="4"
                              maxlength="1000"><?= pdm_e($system['description'] ?? '') ?></textarea>
                </div>

                <div class="pdm-flex pdm-gap-2 pdm-mt-3">
                    <button type="submit" class="btn-pdm-primary">
                        <i class="fas fa-save"></i> ذخیره تغییرات
                    </button>
                    <a href="<?= pdm_url('dashboard') ?>" class="btn-pdm-outline">
                        <i class="fas fa-times"></i> انصراف
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- اطلاعات سیستمی -->
    <div class="card pdm-mt-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle"></i>
                اطلاعات سیستمی
            </h3>
        </div>
        <div class="card-body">
            <table class="pdm-table">
                <tbody>
                    <tr>
                        <th style="width: 200px;">شناسه سیستم</th>
                        <td><?= pdm_num($system['id'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>تاریخ ایجاد</th>
                        <td><?= pdm_date($system['created_at'] ?? null, 'Y/m/d H:i') ?></td>
                    </tr>
                    <tr>
                        <th>وضعیت</th>
                        <td>
                            <?php if (($system['status'] ?? '') === 'active'): ?>
                                <span class="pdm-status-badge pdm-status-active">فعال</span>
                            <?php else: ?>
                                <span class="pdm-status-badge pdm-status-inactive">غیرفعال</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/pdm.js"></script>