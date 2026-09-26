<?php
$system = $system ?? null;
$action = $action ?? 'store';
$isEdit = $action === 'update';
$formAction = $isEdit
    ? CURRENT_MODULE_URL . '?controller=system&action=update&id=' . (int)$system['id']
    : CURRENT_MODULE_URL . '?controller=system&action=store';
?>
<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex-between qc-mb-3">
        <h2>
            <i class="fas fa-<?= $isEdit ? 'edit' : 'plus-circle' ?>"></i>
            <?= $isEdit ? 'ویرایش سیستم کیفیت' : 'ایجاد سیستم کیفیت' ?>
        </h2>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="qc-alert danger qc-mb-3">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="qc-card" style="max-width:760px;">
        <div class="qc-card-body">
            <form method="POST" action="<?= $formAction ?>">

                <div class="qc-form-group">
                    <label>نام شرکت <span class="required">*</span></label>
                    <input type="text" name="company_name" class="qc-form-control" required
                           value="<?= htmlspecialchars($system['company_name'] ?? '') ?>"
                           placeholder="مثلاً: شرکت فولاد مبارکه">
                </div>

                <div class="qc-form-group">
                    <label>صنعت</label>
                    <input type="text" name="industry" class="qc-form-control"
                           value="<?= htmlspecialchars($system['industry'] ?? '') ?>"
                           placeholder="خودروسازی، داروسازی، فولاد، ...">
                    <small class="qc-form-help">نام صنعت برای دسته‌بندی بهتر</small>
                </div>

                <div class="qc-form-group">
                    <label>اندازه سازمان</label>
                    <select name="company_size" class="qc-form-select">
                        <?php
                        $sizes = [
                            'micro' => 'خیلی کوچک', 'small' => 'کوچک', 'medium' => 'متوسط',
                            'large' => 'بزرگ', 'enterprise' => 'سازمانی',
                        ];
                        $current = $system['company_size'] ?? 'medium';
                        foreach ($sizes as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $current === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="qc-form-group">
                    <label>توضیحات</label>
                    <textarea name="description" class="qc-form-textarea"
                              placeholder="توضیحات تکمیلی..."><?= htmlspecialchars($system['description'] ?? '') ?></textarea>
                </div>

                <div class="qc-flex-between qc-mt-3">
                    <a href="<?= CURRENT_MODULE_URL ?>?controller=system" class="btn-qc-outline">
                        <i class="fas fa-times"></i> انصراف
                    </a>
                    <button type="submit" class="btn-qc-primary">
                        <i class="fas fa-save"></i>
                        <?= $isEdit ? 'به‌روزرسانی' : 'ذخیره' ?>
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/quality.js"></script>