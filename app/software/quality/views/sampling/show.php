<?php
use App\Software\Quality\Models\SamplingPlan;

$plan    = $plan    ?? [];
$ocCurve = $ocCurve ?? [];

$n = (int) ($plan['sample_size'] ?? 0);
$c = (int) ($plan['accept_number'] ?? 0);
$r = (int) ($plan['reject_number'] ?? ($c + 1));
?>
<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex-between qc-mb-3">
        <h2>
            <i class="fas fa-vials"></i>
            <?= htmlspecialchars($plan['name'] ?? '—') ?>
        </h2>
        <div class="qc-flex qc-gap-1">
            <span class="qc-chart-badge">
                <?= htmlspecialchars(SamplingPlan::PLAN_TYPES[$plan['plan_type']] ?? $plan['plan_type']) ?>
            </span>
            <a href="<?= CURRENT_MODULE_URL ?>?controller=sampling" class="btn-qc-outline">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>

    <div class="qc-stats-grid qc-mb-3">
        <div class="qc-stat-card success">
            <small><i class="fas fa-vial"></i> حجم نمونه (n)</small>
            <h3><?= $n ?></h3>
        </div>
        <div class="qc-stat-card info">
            <small><i class="fas fa-check-circle"></i> عدد پذیرش (c)</small>
            <h3><?= $c ?></h3>
        </div>
        <div class="qc-stat-card warning">
            <small><i class="fas fa-times-circle"></i> عدد رد (r)</small>
            <h3><?= $r ?></h3>
        </div>
        <div class="qc-stat-card">
            <small><i class="fas fa-cubes"></i> حجم دسته</small>
            <h3><?= (int)($plan['lot_size'] ?? 0) ?></h3>
        </div>
    </div>

    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-chart-line"></i> منحنی OC (Operating Characteristic)</h3>
        </div>
        <div class="qc-card-body">
            <div class="qc-chart-container">
                <canvas id="qc-oc-canvas" style="max-height:400px;"></canvas>
            </div>
        </div>
    </div>

    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-calculator"></i> پارامترها و ریسک‌ها</h3>
        </div>
        <div class="qc-card-body">
            <div class="qc-chart-summary">
                <div class="qc-summary-item">
                    <small>AQL</small>
                    <strong><?= number_format((float)$plan['aql'], 3) ?>%</strong>
                </div>
                <div class="qc-summary-item">
                    <small>LTPD</small>
                    <strong><?= number_format((float)$plan['ltpd'], 3) ?>%</strong>
                </div>
                <div class="qc-summary-item">
                    <small>ریسک تولیدکننده (α واقعی)</small>
                    <strong><?= number_format((float)$plan['producer_risk'], 4) ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>ریسک مصرف‌کننده (β واقعی)</small>
                    <strong><?= number_format((float)$plan['consumer_risk'], 4) ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>AOQL</small>
                    <strong><?= $plan['aoql'] !== null ? number_format((float)$plan['aoql'], 5) : '—' ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>ATI (در AQL)</small>
                    <strong><?= $plan['at_i'] !== null ? number_format((float)$plan['at_i'], 1) : '—' ?></strong>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($plan['description']) || !empty($plan['notes'])): ?>
        <div class="qc-card qc-mb-3">
            <div class="qc-card-header">
                <h3 class="qc-card-title"><i class="fas fa-sticky-note"></i> توضیحات</h3>
            </div>
            <div class="qc-card-body">
                <?php if (!empty($plan['description'])): ?>
                    <p><?= nl2br(htmlspecialchars($plan['description'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($plan['notes'])): ?>
                    <hr>
                    <p class="qc-text-muted"><?= nl2br(htmlspecialchars($plan['notes'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="qc-alert info">
        <i class="fas fa-info-circle"></i>
        <div>
            <strong>تفسیر طرح:</strong><br>
            از هر دسته، <strong><?= $n ?></strong> نمونه انتخاب کنید.
            اگر تعداد معیوب‌ها <strong>≤ <?= $c ?></strong> بود، دسته را <strong>بپذیرید</strong>.
            اگر <strong>≥ <?= $r ?></strong> بود، <strong>رد کنید</strong>.
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    window.QC_OC_CURVE = <?= json_encode($ocCurve) ?>;
</script>
<script src="/public/assets/js/software/quality.js"></script>