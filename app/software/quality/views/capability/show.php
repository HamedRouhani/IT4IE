<?php
use App\Software\Quality\Models\CapabilityStudy;

$study   = $study   ?? [];
$dataset = $dataset ?? null;
$ppm     = $ppm     ?? null;

$cpk = $study['cpk'] !== null ? (float)$study['cpk'] : null;
$verdict = CapabilityStudy::verdict($cpk);
$verdictLabel = CapabilityStudy::verdictLabel($cpk);
$verdictColor = CapabilityStudy::verdictColor($cpk);

$colorMap = [
    'success'   => 'qc-status-active',
    'warning'   => 'qc-status-warning',
    'danger'    => 'qc-status-danger',
    'secondary' => 'qc-status-inactive',
];
$badgeClass = $colorMap[$verdictColor] ?? 'qc-status-inactive';
?>
<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex-between qc-mb-3">
        <h2>
            <i class="fas fa-bullseye"></i>
            تحلیل قابلیت: <?= htmlspecialchars($dataset['name'] ?? '—') ?>
        </h2>
        <div class="qc-flex qc-gap-1">
            <span class="qc-status-badge <?= $badgeClass ?>">
                <?= htmlspecialchars($verdictLabel) ?>
            </span>
            <a href="<?= CURRENT_MODULE_URL ?>?controller=capability" class="btn-qc-outline">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>

    <div class="qc-stats-grid qc-mb-3">
        <div class="qc-stat-card success">
            <small><i class="fas fa-chart-area"></i> Cp</small>
            <h3><?= $study['cp'] !== null ? number_format((float)$study['cp'], 3) : '—' ?></h3>
        </div>
        <div class="qc-stat-card <?= $cpk !== null && $cpk >= 1.33 ? 'success' : ($cpk !== null && $cpk >= 1.00 ? 'warning' : 'danger') ?>">
            <small><i class="fas fa-bullseye"></i> Cpk</small>
            <h3><?= $cpk !== null ? number_format($cpk, 3) : '—' ?></h3>
        </div>
        <div class="qc-stat-card info">
            <small><i class="fas fa-chart-line"></i> Pp</small>
            <h3><?= $study['pp'] !== null ? number_format((float)$study['pp'], 3) : '—' ?></h3>
        </div>
        <div class="qc-stat-card info">
            <small><i class="fas fa-bullseye"></i> Ppk</small>
            <h3><?= $study['ppk'] !== null ? number_format((float)$study['ppk'], 3) : '—' ?></h3>
        </div>
    </div>

    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-calculator"></i> جزئیات آماری</h3>
        </div>
        <div class="qc-card-body">
            <div class="qc-chart-summary">
                <div class="qc-summary-item">
                    <small>میانگین (Mean)</small>
                    <strong><?= $study['mean'] !== null ? number_format((float)$study['mean'], 5) : '—' ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>σ Within (کوتاه‌مدت)</small>
                    <strong><?= $study['std_within'] !== null ? number_format((float)$study['std_within'], 5) : '—' ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>σ Overall (بلندمدت)</small>
                    <strong><?= $study['std_overall'] !== null ? number_format((float)$study['std_overall'], 5) : '—' ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>LSL</small>
                    <strong><?= $study['lsl'] !== null ? number_format((float)$study['lsl'], 3) : '—' ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>USL</small>
                    <strong><?= $study['usl'] !== null ? number_format((float)$study['usl'], 3) : '—' ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>Target</small>
                    <strong><?= $study['target'] !== null ? number_format((float)$study['target'], 3) : '—' ?></strong>
                </div>
            </div>
        </div>
    </div>

    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-chart-bar"></i> شاخص‌های کامل</h3>
        </div>
        <div class="qc-card-body">
            <div class="qc-chart-summary">
                <div class="qc-summary-item">
                    <small>CPU</small>
                    <strong><?= $study['cpu'] !== null ? number_format((float)$study['cpu'], 3) : '—' ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>CPL</small>
                    <strong><?= $study['cpl'] !== null ? number_format((float)$study['cpl'], 3) : '—' ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>Cpm (Taguchi)</small>
                    <strong><?= $study['cpm'] !== null ? number_format((float)$study['cpm'], 3) : '—' ?></strong>
                </div>
            </div>
        </div>
    </div>

    <?php if ($ppm !== null): ?>
        <div class="qc-card qc-mb-3">
            <div class="qc-card-header">
                <h3 class="qc-card-title"><i class="fas fa-exclamation-triangle"></i> تخمین PPM خارج از مشخصات</h3>
            </div>
            <div class="qc-card-body">
                <div class="qc-chart-summary">
                    <div class="qc-summary-item">
                        <small>PPM زیر LSL</small>
                        <strong><?= number_format((float)$ppm['ppm_below'], 2) ?></strong>
                    </div>
                    <div class="qc-summary-item">
                        <small>PPM بالای USL</small>
                        <strong><?= number_format((float)$ppm['ppm_above'], 2) ?></strong>
                    </div>
                    <div class="qc-summary-item" style="border-right-color:#DC2626;">
                        <small>PPM کل</small>
                        <strong style="color:#DC2626;"><?= number_format((float)$ppm['ppm_total'], 2) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="qc-alert <?= $verdict === 'capable' || $verdict === 'excellent' ? 'success' : ($verdict === 'marginal' ? 'warning' : 'danger') ?>">
        <i class="fas fa-info-circle"></i>
        <div>
            <strong>ارزیابی: <?= htmlspecialchars($verdictLabel) ?></strong><br>
            <?php if ($verdict === 'excellent'): ?>
                فرآیند از نظر قابلیت در سطح عالی قرار دارد (Cpk ≥ 1.67).
            <?php elseif ($verdict === 'capable'): ?>
                فرآیند قابلیت لازم را دارد (Cpk ≥ 1.33) — مطابق استاندارد AIAG.
            <?php elseif ($verdict === 'marginal'): ?>
                فرآیند در محدوده مرزی قرار دارد (1.00 ≤ Cpk < 1.33) — نیازمند بهبود.
            <?php else: ?>
                فرآیند قابلیت لازم را ندارد (Cpk < 1.00) — اقدام اصلاحی فوری.
            <?php endif; ?>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/quality.js"></script>