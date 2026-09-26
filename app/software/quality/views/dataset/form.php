<?php
use App\Software\Quality\Models\Dataset;

$dataset     = $dataset     ?? null;
$projects    = $projects    ?? [];
$project     = $project     ?? null;
$chart_types = $chart_types ?? Dataset::CHART_TYPES;
$action      = $action      ?? 'store';
$isEdit      = $action === 'update';

$formAction = $isEdit
    ? CURRENT_MODULE_URL . '?controller=dataset&action=update&id=' . (int)$dataset['id']
    : CURRENT_MODULE_URL . '?controller=dataset&action=store';

$currentChart = $dataset['chart_type'] ?? 'xbar_r';
?>
<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex-between qc-mb-3">
        <h2>
            <i class="fas fa-<?= $isEdit ? 'edit' : 'database' ?>"></i>
            <?= $isEdit ? 'ویرایش دیتاست' : 'ایجاد دیتاست جدید' ?>
        </h2>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="qc-alert danger qc-mb-3">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="qc-card" style="max-width:860px;">
        <div class="qc-card-body">
            <form method="POST" action="<?= $formAction ?>">

                <?php if (!$isEdit): ?>
                    <div class="qc-form-group">
                        <label>پروژه <span class="required">*</span></label>
                        <select name="project_id" class="qc-form-select" required>
                            <option value="">— انتخاب پروژه —</option>
                            <?php
                            $currentProjectId = $project['id'] ?? ($_GET['project_id'] ?? null);
                            foreach ($projects as $p): ?>
                                <option value="<?= (int)$p['id'] ?>"
                                    <?= (int)$currentProjectId === (int)$p['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['name']) ?>
                                    <?= !empty($p['product_name']) ? ' — ' . htmlspecialchars($p['product_name']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="qc-form-group">
                    <label>نام دیتاست <span class="required">*</span></label>
                    <input type="text" name="name" class="qc-form-control" required
                           value="<?= htmlspecialchars($dataset['name'] ?? '') ?>"
                           placeholder="مثلاً: اندازه‌گیری قطر شفت — هفته ۱">
                </div>

                <div class="qc-form-group">
                    <label>نوع نمودار کنترل <span class="required">*</span></label>
                    <select name="chart_type" id="qc-chart-type" class="qc-form-select" required>
                        <?php foreach ($chart_types as $key => $label): ?>
                            <option value="<?= htmlspecialchars($key) ?>"
                                <?= $currentChart === $key ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="qc-form-help">
                        برای داده‌های پیوسته از X̄-R، X̄-S، I-MR و برای داده‌های صفتی از p، np، c، u استفاده کنید.
                    </small>
                </div>

                <div class="qc-form-group" id="qc-variable-fields">
                    <label>حجم زیرگروه (n)</label>
                    <input type="number" name="subgroup_size" class="qc-form-control" min="2" max="10"
                           value="<?= htmlspecialchars($dataset['subgroup_size'] ?? '') ?>"
                           placeholder="بین ۲ و ۱۰">
                    <small class="qc-form-help">برای X̄-R و X̄-S الزامی، برای I-MR مقدار ۱ بگذارید.</small>
                </div>

                <div class="qc-flex qc-gap-2" id="qc-spec-fields">
                    <div class="qc-form-group" style="flex:1;">
                        <label>LSL — حد مشخصه پایین</label>
                        <input type="number" step="any" name="spec_lsl" class="qc-form-control"
                               value="<?= htmlspecialchars($dataset['spec_lsl'] ?? '') ?>"
                               placeholder="مثلاً: 9.95">
                    </div>
                    <div class="qc-form-group" style="flex:1;">
                        <label>USL — حد مشخصه بالا</label>
                        <input type="number" step="any" name="spec_usl" class="qc-form-control"
                               value="<?= htmlspecialchars($dataset['spec_usl'] ?? '') ?>"
                               placeholder="مثلاً: 10.05">
                    </div>
                    <div class="qc-form-group" style="flex:1;">
                        <label>مقدار هدف (Target)</label>
                        <input type="number" step="any" name="spec_target" class="qc-form-control"
                               value="<?= htmlspecialchars($dataset['spec_target'] ?? '') ?>"
                               placeholder="مثلاً: 10.00">
                    </div>
                </div>

                <div class="qc-form-group">
                    <label>یادداشت</label>
                    <textarea name="notes" class="qc-form-textarea"
                              placeholder="توضیحات تکمیلی..."><?= htmlspecialchars($dataset['notes'] ?? '') ?></textarea>
                </div>

                <div class="qc-flex-between qc-mt-3">
                    <a href="<?= CURRENT_MODULE_URL ?>?controller=dataset" class="btn-qc-outline">
                        <i class="fas fa-times"></i> انصراف
                    </a>
                    <button type="submit" class="btn-qc-primary">
                        <i class="fas fa-save"></i>
                        <?= $isEdit ? 'به‌روزرسانی' : 'ذخیره و ادامه' ?>
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/quality.js"></script>