<?php
use App\Software\Quality\Models\MsaStudy;

$study = $study ?? [];
$data  = $data  ?? [];

$grr = $study['pct_grr'] !== null ? (float)$study['pct_grr'] : null;
$verdict = MsaStudy::verdict($grr);
$verdictLabel = MsaStudy::verdictLabel($grr);

$colorMap = [
    'acceptable'   => 'qc-status-active',
    'marginal'     => 'qc-status-warning',
    'unacceptable' => 'qc-status-danger',
    'unknown'      => 'qc-status-inactive',
];
$badgeClass = $colorMap[$verdict] ?? 'qc-status-inactive';
?>
<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex-between qc-mb-3">
        <h2>
            <i class="fas fa-ruler-combined"></i>
            <?= htmlspecialchars($study['name'] ?? '—') ?>
        </h2>
        <div class="qc-flex qc-gap-1">
            <span class="qc-status-badge <?= $badgeClass ?>">
                <?= htmlspecialchars($verdictLabel) ?>
            </span>
            <a href="<?= CURRENT_MODULE_URL ?>?controller=msa" class="btn-qc-outline">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>

    <div class="qc-stats-grid qc-mb-3">
        <div class="qc-stat-card">
            <small><i class="fas fa-ruler"></i> EV (تکرارپذیری)</small>
            <h3><?= $study['ev'] !== null ? number_format((float)$study['ev'], 4) : '—' ?></h3>
        </div>
        <div class="qc-stat-card">
            <small><i class="fas fa-user-check"></i> AV (تکرارپذیری اپراتور)</small>
            <h3><?= $study['av'] !== null ? number_format((float)$study['av'], 4) : '—' ?></h3>
        </div>
        <div class="qc-stat-card <?= $grr !== null && $grr < 10 ? 'success' : ($grr !== null && $grr <= 30 ? 'warning' : 'danger') ?>">
            <small><i class="fas fa-percentage"></i> %GRR</small>
            <h3><?= $grr !== null ? number_format($grr, 2) . '%' : '—' ?></h3>
        </div>
        <div class="qc-stat-card info">
            <small><i class="fas fa-layer-group"></i> ndc</small>
            <h3><?= (int)($study['ndc'] ?? 0) ?></h3>
        </div>
    </div>

    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-chart-bar"></i> اجزای واریانس</h3>
        </div>
        <div class="qc-card-body">
            <div class="qc-chart-summary">
                <div class="qc-summary-item">
                    <small>EV — Equipment Variation</small>
                    <strong><?= $study['ev'] !== null ? number_format((float)$study['ev'], 5) : '—' ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>AV — Appraiser Variation</small>
                    <strong><?= $study['av'] !== null ? number_format((float)$study['av'], 5) : '—' ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>GRR — Gage R&R</small>
                    <strong><?= $study['grr'] !== null ? number_format((float)$study['grr'], 5) : '—' ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>PV — Part Variation</small>
                    <strong><?= $study['pv'] !== null ? number_format((float)$study['pv'], 5) : '—' ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>TV — Total Variation</small>
                    <strong><?= $study['tv'] !== null ? number_format((float)$study['tv'], 5) : '—' ?></strong>
                </div>
            </div>
        </div>
    </div>

    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-percentage"></i> درصد مشارکت هر جزء</h3>
        </div>
        <div class="qc-card-body">
            <div class="qc-chart-summary">
                <div class="qc-summary-item">
                    <small>%EV</small>
                    <strong><?= $study['pct_ev'] !== null ? number_format((float)$study['pct_ev'], 2) . '%' : '—' ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>%AV</small>
                    <strong><?= $study['pct_av'] !== null ? number_format((float)$study['pct_av'], 2) . '%' : '—' ?></strong>
                </div>
                <div class="qc-summary-item" style="border-right-color:#059669;">
                    <small><strong>%GRR</strong></small>
                    <strong style="color:#059669;"><?= $grr !== null ? number_format($grr, 2) . '%' : '—' ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>%PV</small>
                    <strong><?= $study['pct_pv'] !== null ? number_format((float)$study['pct_pv'], 2) . '%' : '—' ?></strong>
                </div>
            </div>
        </div>
    </div>

    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-info-circle"></i> مشخصات مطالعه</h3>
        </div>
        <div class="qc-card-body">
            <div class="qc-chart-summary">
                <div class="qc-summary-item">
                    <small>روش</small>
                    <strong><?= htmlspecialchars(MsaStudy::METHODS[$study['method']] ?? $study['method']) ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>تعداد قطعات</small>
                    <strong><?= (int)($study['num_parts'] ?? 0) ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>تعداد اپراتورها</small>
                    <strong><?= (int)($study['num_operators'] ?? 0) ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>تعداد تکرار</small>
                    <strong><?= (int)($study['num_trials'] ?? 0) ?></strong>
                </div>
            </div>
        </div>
    </div>

    <div class="qc-alert <?= $verdict === 'acceptable' ? 'success' : ($verdict === 'marginal' ? 'warning' : 'danger') ?>">
        <i class="fas fa-info-circle"></i>
        <div>
            <strong>ارزیابی سیستم اندازه‌گیری: <?= htmlspecialchars($verdictLabel) ?></strong><br>
            <?php if ($verdict === 'acceptable'): ?>
                سیستم اندازه‌گیری قابل قبول است (%GRR < 10%) — مطابق استاندارد AIAG MSA.
            <?php elseif ($verdict === 'marginal'): ?>
                سیستم اندازه‌گیری در محدوده مرزی است (10% ≤ %GRR ≤ 30%) — قابل قبول مشروط به شرایط.
            <?php else: ?>
                سیستم اندازه‌گیری غیرقابل قبول است (%GRR > 30%) — نیازمند بهبود فوری.
            <?php endif; ?>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/quality.js"></script>