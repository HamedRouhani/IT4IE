<?php
/**
 * PdM Analyzer - فرم ایجاد دسته‌بندی
 * مسیر: app/software/pdm/views/category/create.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-plus-circle"></i> افزودن دسته‌بندی جدید
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                دسته‌بندی جدید را در ساختار درختی اضافه کنید.
            </p>
        </div>
        <a href="<?= pdm_url('asset_category') ?>" class="btn-pdm-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="pdm-alert <?= pdm_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= pdm_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle"></i>
                اطلاعات دسته‌بندی
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= pdm_url('asset_category', 'store') ?>">
                <?= $this->csrfField() ?>

                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="name">
                        نام دسته <span style="color: var(--pdm-danger);">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           class="pdm-form-control"
                           placeholder="مثال: پمپ‌ها"
                           required
                           maxlength="150">
                </div>

                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="parent_id">دسته والد</label>
                    <select id="parent_id" name="parent_id" class="pdm-form-control">
                        <option value="">— بدون والد (ریشه) —</option>
                        <?php foreach ($parentOptions as $opt): ?>
                            <option value="<?= (int) $opt['id'] ?>">
                                <?= str_repeat('— ', (int) ($opt['level'] ?? 0)) . pdm_e($opt['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="description">توضیحات</label>
                    <textarea id="description"
                              name="description"
                              class="pdm-form-control"
                              rows="3"
                              maxlength="1000"></textarea>
                </div>

                <div class="pdm-flex pdm-gap-2 pdm-mt-3">
                    <button type="submit" class="btn-pdm-primary">
                        <i class="fas fa-save"></i> ذخیره
                    </button>
                    <a href="<?= pdm_url('asset_category') ?>" class="btn-pdm-outline">
                        <i class="fas fa-times"></i> انصراف
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/pdm.js"></script>