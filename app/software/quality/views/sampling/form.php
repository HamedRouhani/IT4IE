<?php
use App\Software\Quality\Models\SamplingPlan;

$projects  = $projects  ?? [];
$planTypes = $planTypes ?? SamplingPlan::PLAN_TYPES;
$plan      = $plan      ?? null;
?>
<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex-between qc-mb-3">
        <h2><i class="fas fa-vials"></i> ایجاد طرح نمونه‌گیری</h2>
        <a href="<?= CURRENT_MODULE_URL ?>?controller=sampling" class="btn-qc-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="qc-alert danger qc-mb-3">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-info-circle"></i> مشخصات طرح</h3>
        </div>
        <div class="qc-card-body">
            <div class="qc-flex qc-gap-2">
                <div class="qc-form-group" style="flex:2;">
                    <label>نام طرح <span class="required">*</span></label>
                    <input type="text" id="qc-sample-name" class="qc-form-control"
                           placeholder="مثلاً: نمونه‌گیری پیچ M8 — AQL 1.0">
                </div>
                <div class="qc-form-group" style="flex:1;">
                    <label>پروژه (اختیاری)</label>
                    <select id="qc-sample-project" class="qc-form-select">
                        <option value="">— بدون پروژه —</option>
                        <?php foreach ($projects as $p): ?>
                            <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="qc-form-group">
                <label>نوع طرح</label>
                <select id="qc-sample-type" class="qc-form-select">
                    <?php foreach ($planTypes as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $k === 'single' ? 'selected' : '' ?>>
                            <?= htmlspecialchars($v) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-sliders-h"></i> پارامترها</h3>
        </div>
        <div class="qc-card-body">
            <div class="qc-flex qc-gap-2">
                <div class="qc-form-group" style="flex:1;">
                    <label>حجم دسته (Lot Size) <span class="required">*</span></label>
                    <input type="number" id="qc-sample-lot" class="qc-form-control"
                           placeholder="مثلاً: 1000" min="1" value="1000">
                </div>
                <div class="qc-form-group" style="flex:1;">
                    <label>AQL (%) <span class="required">*</span></label>
                    <input type="number" step="any" id="qc-sample-aql" class="qc-form-control"
                           placeholder="مثلاً: 1.0" min="0.01" value="1.0">
                    <small class="qc-form-help">سطح کیفیت قابل قبول</small>
                </div>
                <div class="qc-form-group" style="flex:1;">
                    <label>LTPD (%) <span class="required">*</span></label>
                    <input type="number" step="any" id="qc-sample-ltpd" class="qc-form-control"
                           placeholder="مثلاً: 5.0" min="0.01" value="5.0">
                    <small class="qc-form-help">سطح کیفیت غیرقابل قبول</small>
                </div>
            </div>

            <div class="qc-flex qc-gap-2">
                <div class="qc-form-group" style="flex:1;">
                    <label>ریسک تولیدکننده (α)</label>
                    <input type="number" step="any" id="qc-sample-alpha" class="qc-form-control"
                           value="0.05" min="0.001" max="0.5">
                    <small class="qc-form-help">پیش‌فرض: 0.05 (5%)</small>
                </div>
                <div class="qc-form-group" style="flex:1;">
                    <label>ریسک مصرف‌کننده (β)</label>
                    <input type="number" step="any" id="qc-sample-beta" class="qc-form-control"
                           value="0.10" min="0.001" max="0.5">
                    <small class="qc-form-help">پیش‌فرض: 0.10 (10%)</small>
                </div>
            </div>

            <div class="qc-alert info qc-mb-2">
                <i class="fas fa-info-circle"></i>
                <span>اگر n و c را خالی بگذارید، سیستم به‌طور خودکار بهترین طرح را پیشنهاد می‌دهد.</span>
            </div>

            <div class="qc-flex qc-gap-2">
                <div class="qc-form-group" style="flex:1;">
                    <label>حجم نمونه (n) — اختیاری</label>
                    <input type="number" id="qc-sample-n" class="qc-form-control"
                           placeholder="خالی = محاسبه خودکار" min="2">
                </div>
                <div class="qc-form-group" style="flex:1;">
                    <label>عدد پذیرش (c) — اختیاری</label>
                    <input type="number" id="qc-sample-c" class="qc-form-control"
                           placeholder="خالی = محاسبه خودکار" min="0">
                </div>
            </div>
        </div>
    </div>

    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-sticky-note"></i> توضیحات</h3>
        </div>
        <div class="qc-card-body">
            <div class="qc-form-group">
                <label>توضیحات</label>
                <textarea id="qc-sample-desc" class="qc-form-textarea"
                          placeholder="توضیحات تکمیلی..."></textarea>
            </div>
            <div class="qc-form-group">
                <label>یادداشت</label>
                <textarea id="qc-sample-notes" class="qc-form-textarea"
                          placeholder="یادداشت‌های داخلی..."></textarea>
            </div>
        </div>
    </div>

    <div class="qc-flex-between">
        <a href="<?= CURRENT_MODULE_URL ?>?controller=sampling" class="btn-qc-outline">
            <i class="fas fa-times"></i> انصراف
        </a>
        <button type="button" class="btn-qc-primary" id="qc-sample-save">
            <i class="fas fa-save"></i> محاسبه و ذخیره
        </button>
    </div>

</div>

<script src="/public/assets/js/software/quality.js"></script>