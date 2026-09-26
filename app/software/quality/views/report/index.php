<?php
/**
 * Quality Analyzer — Reports
 * مسیر: quality/views/report/index.php
 */

use App\Software\Quality\Models\CapabilityStudy;
use App\Software\Quality\Models\MsaStudy;
use App\Software\Quality\Models\SamplingPlan;

$system          = $system          ?? [];
$stats           = $stats           ?? [];
$projects        = $projects        ?? [];
$allCharts       = $allCharts       ?? [];
$outOfControl    = (int)($outOfControl ?? 0);
$chartTypes      = $chartTypes      ?? ['variable' => 0, 'attribute' => 0];
$capabilities    = $capabilities    ?? [];
$avgCpk          = $avgCpk          ?? null;
$capableCount    = (int)($capableCount    ?? 0);
$marginalCount   = (int)($marginalCount   ?? 0);
$notCapableCount = (int)($notCapableCount ?? 0);
$msaStudies      = $msaStudies      ?? [];
$avgGrr          = $avgGrr          ?? null;
$msaAcceptable   = (int)($msaAcceptable   ?? 0);
$msaMarginal     = (int)($msaMarginal     ?? 0);
$msaUnacceptable = (int)($msaUnacceptable ?? 0);
$samplingPlans   = $samplingPlans   ?? [];
$datasets        = $datasets        ?? [];

$stats = array_merge([
    'projects'   => 0,
    'datasets'   => 0,
    'charts'     => 0,
    'capability' => 0,
    'msa'        => 0,
], $stats);
?>
<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex-between qc-mb-3">
        <h2>
            <i class="fas fa-file-alt"></i> گزارش‌های کیفیت
            <small class="qc-text-muted" style="font-size:.75rem;font-weight:500;">
                — <?= htmlspecialchars($system['company_name'] ?? '') ?>
            </small>
        </h2>
        <button type="button" class="btn-qc-outline qc-no-print" onclick="window.print()">
            <i class="fas fa-print"></i> چاپ گزارش
        </button>
    </div>

    <!-- ═══════════════════════════════════════════
         خلاصه کلی سیستم
         ═══════════════════════════════════════════ -->
    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-chart-pie"></i> خلاصه کلی سیستم</h3>
        </div>
        <div class="qc-card-body">
            <div class="qc-stats-grid">
                <div class="qc-stat-card">
                    <small><i class="fas fa-folder"></i> پروژه‌ها</small>
                    <h3><?= (int) $stats['projects'] ?></h3>
                </div>
                <div class="qc-stat-card info">
                    <small><i class="fas fa-database"></i> دیتاست‌ها</small>
                    <h3><?= (int) $stats['datasets'] ?></h3>
                </div>
                <div class="qc-stat-card success">
                    <small><i class="fas fa-chart-line"></i> نمودارها</small>
                    <h3><?= (int) $stats['charts'] ?></h3>
                </div>
                <div class="qc-stat-card warning">
                    <small><i class="fas fa-bullseye"></i> تحلیل قابلیت</small>
                    <h3><?= (int) $stats['capability'] ?></h3>
                </div>
                <div class="qc-stat-card">
                    <small><i class="fas fa-ruler-combined"></i> مطالعات MSA</small>
                    <h3><?= (int) $stats['msa'] ?></h3>
                </div>
                <div class="qc-stat-card">
                    <small><i class="fas fa-vials"></i> طرح‌های نمونه‌گیری</small>
                    <h3><?= count($samplingPlans) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════
         خلاصه نمودارهای کنترل
         ═══════════════════════════════════════════ -->
    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-chart-line"></i> خلاصه نمودارهای کنترل</h3>
        </div>
        <div class="qc-card-body">
            <div class="qc-stats-grid">
                <div class="qc-stat-card success">
                    <small><i class="fas fa-check-circle"></i> در کنترل</small>
                    <h3><?= count($allCharts) - $outOfControl ?></h3>
                </div>
                <div class="qc-stat-card danger">
                    <small><i class="fas fa-exclamation-triangle"></i> خارج از کنترل</small>
                    <h3><?= $outOfControl ?></h3>
                </div>
                <div class="qc-stat-card info">
                    <small><i class="fas fa-chart-line"></i> متغیر (Variable)</small>
                    <h3><?= (int) $chartTypes['variable'] ?></h3>
                </div>
                <div class="qc-stat-card warning">
                    <small><i class="fas fa-chart-bar"></i> صفتی (Attribute)</small>
                    <h3><?= (int) $chartTypes['attribute'] ?></h3>
                </div>
            </div>

            <?php if (!empty($allCharts)): ?>
                <h4 class="qc-mt-3" style="font-size:.95rem;">لیست نمودارها:</h4>
                <table class="qc-table">
                    <thead>
                        <tr>
                            <th>دیتاست</th>
                            <th>پروژه</th>
                            <th>نوع</th>
                            <th>CL</th>
                            <th>UCL</th>
                            <th>LCL</th>
                            <th>وضعیت</th>
                            <th>تاریخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allCharts as $c): ?>
                            <?php
                            $ct = $c['chart_type'] ?? '';
                            $isAttr = in_array($ct, ['p', 'np', 'c', 'u'], true);
                            $link = $isAttr ? 'attribute_chart' : 'control_chart';
                            ?>
                            <tr>
                                <td>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=<?= $link ?>&action=show&id=<?= (int)$c['id'] ?>">
                                        <?= htmlspecialchars($c['dataset_name'] ?? '—') ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($c['project_name'] ?? '—') ?></td>
                                <td><code><?= htmlspecialchars($ct) ?></code></td>
                                <td><?= $c['center_line'] !== null ? number_format((float)$c['center_line'], 4) : '—' ?></td>
                                <td><?= $c['ucl'] !== null ? number_format((float)$c['ucl'], 4) : '—' ?></td>
                                <td><?= $c['lcl'] !== null ? number_format((float)$c['lcl'], 4) : '—' ?></td>
                                <td>
                                    <?php if ((int)$c['in_control'] === 1): ?>
                                        <span class="qc-status-badge qc-status-active">در کنترل</span>
                                    <?php else: ?>
                                        <span class="qc-status-badge qc-status-danger">خارج کنترل</span>
                                    <?php endif; ?>
                                </td>
                                <td class="qc-text-muted" style="font-size:.78rem;">
                                    <?= htmlspecialchars($c['computed_at'] ?? '—') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════
         خلاصه تحلیل قابلیت
         ═══════════════════════════════════════════ -->
    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-bullseye"></i> خلاصه تحلیل قابلیت</h3>
        </div>
        <div class="qc-card-body">
            <div class="qc-stats-grid">
                <div class="qc-stat-card <?= $avgCpk !== null && $avgCpk >= 1.33 ? 'success' : ($avgCpk !== null && $avgCpk >= 1.00 ? 'warning' : 'danger') ?>">
                    <small><i class="fas fa-chart-area"></i> میانگین Cpk</small>
                    <h3><?= $avgCpk !== null ? number_format((float)$avgCpk, 2) : '—' ?></h3>
                </div>
                <div class="qc-stat-card success">
                    <small><i class="fas fa-check"></i> قابل قبول (Cpk ≥ 1.33)</small>
                    <h3><?= $capableCount ?></h3>
                </div>
                <div class="qc-stat-card warning">
                    <small><i class="fas fa-exclamation"></i> مرزی (1 ≤ Cpk < 1.33)</small>
                    <h3><?= $marginalCount ?></h3>
                </div>
                <div class="qc-stat-card danger">
                    <small><i class="fas fa-times"></i> ناتوان (Cpk < 1)</small>
                    <h3><?= $notCapableCount ?></h3>
                </div>
            </div>

            <?php if (!empty($capabilities)): ?>
                <h4 class="qc-mt-3" style="font-size:.95rem;">لیست تحلیل‌ها:</h4>
                <table class="qc-table">
                    <thead>
                        <tr>
                            <th>دیتاست</th>
                            <th>پروژه</th>
                            <th>LSL</th>
                            <th>USL</th>
                            <th>Cp</th>
                            <th>Cpk</th>
                            <th>وضعیت</th>
                            <th>تاریخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($capabilities as $s): ?>
                            <?php
                            $cpk = $s['cpk'] !== null ? (float)$s['cpk'] : null;
                            $cls = 'qc-status-inactive';
                            if ($cpk !== null) {
                                if ($cpk >= 1.33)     $cls = 'qc-status-active';
                                elseif ($cpk >= 1.00) $cls = 'qc-status-warning';
                                else                  $cls = 'qc-status-danger';
                            }
                            ?>
                            <tr>
                                <td>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=capability&action=show&id=<?= (int)$s['id'] ?>">
                                        <?= htmlspecialchars($s['dataset_name'] ?? '—') ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($s['project_name'] ?? '—') ?></td>
                                <td><?= $s['lsl'] !== null ? number_format((float)$s['lsl'], 3) : '—' ?></td>
                                <td><?= $s['usl'] !== null ? number_format((float)$s['usl'], 3) : '—' ?></td>
                                <td><?= $s['cp']  !== null ? number_format((float)$s['cp'], 3)  : '—' ?></td>
                                <td><strong><?= $cpk !== null ? number_format($cpk, 3) : '—' ?></strong></td>
                                <td>
                                    <span class="qc-status-badge <?= $cls ?>">
                                        <?= htmlspecialchars(CapabilityStudy::verdictLabel($cpk)) ?>
                                    </span>
                                </td>
                                <td class="qc-text-muted" style="font-size:.78rem;">
                                    <?= htmlspecialchars($s['created_at'] ?? '—') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════
         خلاصه MSA
         ═══════════════════════════════════════════ -->
    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-ruler-combined"></i> خلاصه مطالعات MSA</h3>
        </div>
        <div class="qc-card-body">
            <div class="qc-stats-grid">
                <div class="qc-stat-card <?= $avgGrr !== null && $avgGrr < 10 ? 'success' : ($avgGrr !== null && $avgGrr <= 30 ? 'warning' : 'danger') ?>">
                    <small><i class="fas fa-percentage"></i> میانگین %GRR</small>
                    <h3><?= $avgGrr !== null ? number_format((float)$avgGrr, 2) . '%' : '—' ?></h3>
                </div>
                <div class="qc-stat-card success">
                    <small><i class="fas fa-check"></i> قابل قبول (%GRR < 10)</small>
                    <h3><?= $msaAcceptable ?></h3>
                </div>
                <div class="qc-stat-card warning">
                    <small><i class="fas fa-exclamation"></i> مرزی (10-30)</small>
                    <h3><?= $msaMarginal ?></h3>
                </div>
                <div class="qc-stat-card danger">
                    <small><i class="fas fa-times"></i> غیرقابل قبول (> 30)</small>
                    <h3><?= $msaUnacceptable ?></h3>
                </div>
            </div>

            <?php if (!empty($msaStudies)): ?>
                <h4 class="qc-mt-3" style="font-size:.95rem;">لیست مطالعات:</h4>
                <table class="qc-table">
                    <thead>
                        <tr>
                            <th>نام مطالعه</th>
                            <th>پروژه</th>
                            <th>قطعات</th>
                            <th>اپراتورها</th>
                            <th>تکرار</th>
                            <th>%GRR</th>
                            <th>ndc</th>
                            <th>وضعیت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($msaStudies as $m): ?>
                            <?php
                            $grr = $m['pct_grr'] !== null ? (float)$m['pct_grr'] : null;
                            $cls = 'qc-status-inactive';
                            if ($grr !== null) {
                                if ($grr < 10)        $cls = 'qc-status-active';
                                elseif ($grr <= 30)   $cls = 'qc-status-warning';
                                else                  $cls = 'qc-status-danger';
                            }
                            ?>
                            <tr>
                                <td>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=msa&action=show&id=<?= (int)$m['id'] ?>">
                                        <?= htmlspecialchars($m['name'] ?? '—') ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($m['project_name'] ?? '—') ?></td>
                                <td><?= (int)($m['num_parts'] ?? 0) ?></td>
                                <td><?= (int)($m['num_operators'] ?? 0) ?></td>
                                <td><?= (int)($m['num_trials'] ?? 0) ?></td>
                                <td><strong><?= $grr !== null ? number_format($grr, 2) . '%' : '—' ?></strong></td>
                                <td><?= (int)($m['ndc'] ?? 0) ?></td>
                                <td>
                                    <span class="qc-status-badge <?= $cls ?>">
                                        <?= htmlspecialchars(MsaStudy::verdictLabel($grr)) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════
         لیست پروژه‌ها
         ═══════════════════════════════════════════ -->
    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-folder"></i> لیست پروژه‌ها</h3>
        </div>
        <div class="qc-card-body" style="padding:0;">
            <?php if (empty($projects)): ?>
                <div class="qc-empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>هیچ پروژه‌ای ثبت نشده</p>
                </div>
            <?php else: ?>
                <table class="qc-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام پروژه</th>
                            <th>محصول</th>
                            <th>فرآیند</th>
                            <th>CTQ</th>
                            <th>دیتاست</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $i => $p): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=project&action=show&id=<?= (int)$p['id'] ?>">
                                        <?= htmlspecialchars($p['name']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($p['product_name'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($p['process_name'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($p['ctq'] ?? '—') ?></td>
                                <td><?= (int)($p['dataset_count'] ?? 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════
         لیست دیتاست‌ها
         ═══════════════════════════════════════════ -->
    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-database"></i> لیست دیتاست‌ها (<?= count($datasets) ?>)</h3>
        </div>
        <div class="qc-card-body" style="padding:0;">
            <?php if (empty($datasets)): ?>
                <div class="qc-empty-state">
                    <i class="fas fa-database"></i>
                    <p>هیچ دیتاستی ثبت نشده</p>
                </div>
            <?php else: ?>
                <table class="qc-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام</th>
                            <th>نوع</th>
                            <th>LSL</th>
                            <th>USL</th>
                            <th>تاریخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($datasets as $i => $d): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=dataset&action=data&id=<?= (int)$d['id'] ?>">
                                        <?= htmlspecialchars($d['name']) ?>
                                    </a>
                                </td>
                                <td><code><?= htmlspecialchars($d['chart_type']) ?></code></td>
                                <td><?= $d['spec_lsl'] !== null ? number_format((float)$d['spec_lsl'], 3) : '—' ?></td>
                                <td><?= $d['spec_usl'] !== null ? number_format((float)$d['spec_usl'], 3) : '—' ?></td>
                                <td class="qc-text-muted" style="font-size:.78rem;">
                                    <?= htmlspecialchars($d['created_at'] ?? '—') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════
         لیست طرح‌های نمونه‌گیری
         ═══════════════════════════════════════════ -->
    <?php if (!empty($samplingPlans)): ?>
        <div class="qc-card qc-mb-3">
            <div class="qc-card-header">
                <h3 class="qc-card-title"><i class="fas fa-vials"></i> لیست طرح‌های نمونه‌گیری</h3>
            </div>
            <div class="qc-card-body" style="padding:0;">
                <table class="qc-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام طرح</th>
                            <th>AQL</th>
                            <th>LTPD</th>
                            <th>n</th>
                            <th>c</th>
                            <th>α</th>
                            <th>β</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($samplingPlans as $i => $p): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=sampling&action=show&id=<?= (int)$p['id'] ?>">
                                        <?= htmlspecialchars($p['name']) ?>
                                    </a>
                                </td>
                                <td><?= $p['aql']  !== null ? number_format((float)$p['aql'], 2) : '—' ?></td>
                                <td><?= $p['ltpd'] !== null ? number_format((float)$p['ltpd'], 2) : '—' ?></td>
                                <td><?= (int)($p['sample_size'] ?? 0) ?></td>
                                <td><?= (int)($p['accept_number'] ?? 0) ?></td>
                                <td><?= $p['producer_risk'] !== null ? number_format((float)$p['producer_risk'], 4) : '—' ?></td>
                                <td><?= $p['consumer_risk'] !== null ? number_format((float)$p['consumer_risk'], 4) : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>

<script src="/public/assets/js/software/quality.js"></script>