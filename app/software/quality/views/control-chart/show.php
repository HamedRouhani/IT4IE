<?php
use App\Software\Quality\Models\Dataset;

$chart      = $chart      ?? [];
$dataset    = $dataset    ?? null;
$violations = $violations ?? ['nelson' => [], 'we' => []];
$grouped    = $grouped    ?? [];
$attrData   = $attrData   ?? [];

$chartType = $chart['chart_type'] ?? '';
$inControl = (int)($chart['in_control'] ?? 1) === 1;

$series = [];
if (!empty($grouped)) {
    foreach ($grouped as $sg) {
        $series[] = round(array_sum($sg) / count($sg), 6);
    }
} else if (!empty($attrData) && $dataset) {
    foreach ($attrData as $row) {
        $n = (int) $row['sample_size'];
        $d = (int) $row['defectives'];
        $c = (int) $row['defects'];

        if ($chartType === 'p')       $series[] = $n > 0 ? round($d / $n, 6) : 0;
        elseif ($chartType === 'np')  $series[] = $d;
        elseif ($chartType === 'c')   $series[] = $c;
        elseif ($chartType === 'u')   $series[] = $n > 0 ? round($c / $n, 6) : 0;
    }
}

$cl    = (float)($chart['center_line'] ?? 0);
$ucl   = (float)($chart['ucl'] ?? 0);
$lcl   = (float)($chart['lcl'] ?? 0);
$sigma = (float)($chart['sigma_hat'] ?? 0);

$chartTypeLabels = Dataset::CHART_TYPES;
$nelsonViolations = $violations['nelson'] ?? [];
?>
<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex-between qc-mb-3">
        <h2>
            <i class="fas fa-chart-line"></i>
            نمودار کنترل: <?= htmlspecialchars($dataset['name'] ?? '—') ?>
        </h2>
        <div class="qc-flex qc-gap-1">
            <?php if ($inControl): ?>
                <span class="qc-status-badge qc-status-active">
                    <i class="fas fa-check"></i> فرآیند در کنترل
                </span>
            <?php else: ?>
                <span class="qc-status-badge qc-status-danger">
                    <i class="fas fa-exclamation-triangle"></i> خارج از کنترل
                </span>
            <?php endif; ?>
            <a href="<?= CURRENT_MODULE_URL ?>?controller=control_chart&action=compute&id=<?= (int)$chart['dataset_id'] ?>"
               class="btn-qc-outline">
                <i class="fas fa-sync-alt"></i> محاسبه مجدد
            </a>
        </div>
    </div>

    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-calculator"></i> خلاصه محاسبات</h3>
            <span class="qc-chart-badge">
                <?= htmlspecialchars($chartTypeLabels[$chartType] ?? $chartType) ?>
            </span>
        </div>
        <div class="qc-card-body">
            <div class="qc-chart-summary">
                <div class="qc-summary-item">
                    <small>Center Line (CL)</small>
                    <strong><?= number_format($cl, 4) ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>UCL</small>
                    <strong><?= number_format($ucl, 4) ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>LCL</small>
                    <strong><?= number_format($lcl, 4) ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>σ̂ (Sigma Hat)</small>
                    <strong><?= number_format($sigma, 4) ?></strong>
                </div>
                <?php if (!empty($chart['r_bar'])): ?>
                    <div class="qc-summary-item">
                        <small>R̄</small>
                        <strong><?= number_format((float)$chart['r_bar'], 4) ?></strong>
                    </div>
                <?php endif; ?>
                <?php if (!empty($chart['s_bar'])): ?>
                    <div class="qc-summary-item">
                        <small>S̄</small>
                        <strong><?= number_format((float)$chart['s_bar'], 4) ?></strong>
                    </div>
                <?php endif; ?>
                <?php if (!empty($chart['mr_bar'])): ?>
                    <div class="qc-summary-item">
                        <small>MR̄</small>
                        <strong><?= number_format((float)$chart['mr_bar'], 4) ?></strong>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-chart-area"></i> نمودار</h3>
        </div>
        <div class="qc-card-body">
            <div class="qc-chart-container">
                <canvas id="qc-chart-canvas"
                        data-series='<?= json_encode($series) ?>'
                        data-cl="<?= $cl ?>"
                        data-ucl="<?= $ucl ?>"
                        data-lcl="<?= $lcl ?>"
                        data-title="نمودار <?= htmlspecialchars($chartTypeLabels[$chartType] ?? $chartType) ?>">
                </canvas>
            </div>
        </div>
    </div>

    <?php if (!empty($nelsonViolations)): ?>
        <div class="qc-card qc-mb-3">
            <div class="qc-card-header">
                <h3 class="qc-card-title"><i class="fas fa-exclamation-triangle"></i> قوانین نقض‌شده (<?= count($nelsonViolations) ?>)</h3>
            </div>
            <div class="qc-card-body">
                <div class="qc-violations-list">
                    <?php foreach ($nelsonViolations as $v): ?>
                        <div class="qc-violation-item rule-<?= (int)($v['rule'] ?? 1) ?>">
                            <i class="fas fa-exclamation-circle"></i>
                            <div>
                                <span class="rule-label">قانون <?= (int)($v['rule'] ?? '?') ?>:</span>
                                <?= htmlspecialchars($v['desc'] ?? '') ?>
                                <?php if (!empty($v['points'])): ?>
                                    <br><small class="qc-text-muted">
                                        نقاط: <?= implode('، ', array_map('intval', (array)$v['points'])) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="qc-alert success qc-mb-3">
            <i class="fas fa-check-circle"></i>
            <span>هیچ قانونی از قوانین نلسون نقض نشده — فرآیند در کنترل آماری است.</span>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="/public/assets/js/software/quality.js"></script>