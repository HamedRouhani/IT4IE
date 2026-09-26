<?php
use App\Software\Quality\Models\MsaStudy;

$projects = $projects ?? [];
$methods  = $methods  ?? MsaStudy::METHODS;
?>
<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex-between qc-mb-3">
        <h2><i class="fas fa-ruler-combined"></i> ایجاد مطالعه MSA (Gage R&R)</h2>
        <a href="<?= CURRENT_MODULE_URL ?>?controller=msa" class="btn-qc-outline">
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
            <h3 class="qc-card-title"><i class="fas fa-info-circle"></i> مشخصات مطالعه</h3>
        </div>
        <div class="qc-card-body">
            <div class="qc-flex qc-gap-2">
                <div class="qc-form-group" style="flex:2;">
                    <label>نام مطالعه <span class="required">*</span></label>
                    <input type="text" id="qc-msa-name" class="qc-form-control"
                           placeholder="مثلاً: Gage R&R کولیس دیجیتال — هفته ۱">
                </div>
                <div class="qc-form-group" style="flex:1;">
                    <label>پروژه <span class="required">*</span></label>
                    <select id="qc-msa-project" class="qc-form-select">
                        <option value="">— انتخاب پروژه —</option>
                        <?php foreach ($projects as $p): ?>
                            <option value="<?= (int)$p['id'] ?>">
                                <?= htmlspecialchars($p['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="qc-flex qc-gap-2">
                <div class="qc-form-group" style="flex:1;">
                    <label>روش تحلیل</label>
                    <select id="qc-msa-method" class="qc-form-select">
                        <?php foreach ($methods as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $k === 'anova' ? 'selected' : '' ?>>
                                <?= htmlspecialchars($v) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="qc-form-group" style="flex:1;">
                    <label>تعداد قطعات <span class="required">*</span></label>
                    <input type="number" id="qc-msa-parts" class="qc-form-control" min="2" max="20" value="10">
                </div>
                <div class="qc-form-group" style="flex:1;">
                    <label>تعداد اپراتورها <span class="required">*</span></label>
                    <input type="number" id="qc-msa-operators" class="qc-form-control" min="2" max="5" value="3">
                </div>
                <div class="qc-form-group" style="flex:1;">
                    <label>تعداد تکرار <span class="required">*</span></label>
                    <input type="number" id="qc-msa-trials" class="qc-form-control" min="2" max="5" value="2">
                </div>
            </div>
        </div>
    </div>

    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-table"></i> ماتریس داده‌ها</h3>
            <button type="button" class="btn-qc-primary" id="qc-msa-calculate">
                <i class="fas fa-calculator"></i> محاسبه Gage R&R
            </button>
        </div>
        <div class="qc-card-body">
            <p class="qc-text-muted qc-mb-2" style="font-size:.85rem;">
                در هر سلول، مقدار اندازه‌گیری‌شده را وارد کنید.
            </p>
            <div id="qc-msa-matrix"
                 data-parts="10"
                 data-operators="3"
                 data-trials="2">
            </div>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/quality.js"></script>