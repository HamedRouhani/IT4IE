<?php
$project = $project ?? null;
$action  = $action  ?? 'store';
$isEdit  = $action === 'update';
$formAction = $isEdit
    ? CURRENT_MODULE_URL . '?controller=project&action=update&id=' . (int)$project['id']
    : CURRENT_MODULE_URL . '?controller=project&action=store';
?>
<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex-between qc-mb-3">
        <h2>
            <i class="fas fa-<?= $isEdit ? 'edit' : 'folder-plus' ?>"></i>
            <?= $isEdit ? 'ویرایش پروژه' : 'ایجاد پروژه جدید' ?>
        </h2>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="qc-alert danger qc-mb-3">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="qc-card" style="max-width:820px;">
        <div class="qc-card-body">
            <form method="POST" action="<?= $formAction ?>">

                <div class="qc-flex qc-gap-2">
                    <div class="qc-form-group" style="flex:1;">
                        <label>نام پروژه <span class="required">*</span></label>
                        <input type="text" name="name" class="qc-form-control" required
                               value="<?= htmlspecialchars($project['name'] ?? '') ?>"
                               placeholder="مثلاً: کنترل ابعاد شفت">
                    </div>
                    <div class="qc-form-group" style="flex:1;">
                        <label>نام محصول</label>
                        <input type="text" name="product_name" class="qc-form-control"
                               value="<?= htmlspecialchars($project['product_name'] ?? '') ?>"
                               placeholder="نام محصول تولیدی">
                    </div>
                </div>

                <div class="qc-flex qc-gap-2">
                    <div class="qc-form-group" style="flex:1;">
                        <label>نام فرآیند</label>
                        <input type="text" name="process_name" class="qc-form-control"
                               value="<?= htmlspecialchars($project['process_name'] ?? '') ?>"
                               placeholder="مثلاً: سنگ‌زنی">
                    </div>
                    <div class="qc-form-group" style="flex:1;">
                        <label>واحد اندازه‌گیری</label>
                        <input type="text" name="unit" class="qc-form-control"
                               value="<?= htmlspecialchars($project['unit'] ?? '') ?>"
                               placeholder="mm، kg، °C">
                    </div>
                </div>

                <div class="qc-form-group">
                    <label>CTQ (Critical to Quality)</label>
                    <input type="text" name="ctq" class="qc-form-control"
                           value="<?= htmlspecialchars($project['ctq'] ?? '') ?>"
                           placeholder="مشخصه بحرانی کیفیت — مثلاً: قطر شفت">
                    <small class="qc-form-help">مشخصه‌ای که مستقیماً بر رضایت مشتری اثر دارد</small>
                </div>

                <div class="qc-form-group">
                    <label>توضیحات</label>
                    <textarea name="description" class="qc-form-textarea"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
                </div>

                <div class="qc-flex-between qc-mt-3">
                    <a href="<?= CURRENT_MODULE_URL ?>?controller=project" class="btn-qc-outline">
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