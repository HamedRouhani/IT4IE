<?php
/**
 * Quality Analyzer — Dashboard
 * مسیر: quality/views/dashboard/index.php
 */

use App\Software\Quality\Models\CapabilityStudy;
use App\Software\Quality\Models\MsaStudy;

$system = $system ?? [];
$stats  = $stats  ?? [];
$recentCharts       = $recentCharts       ?? [];
$recentCapabilities = $recentCapabilities ?? [];
$recentMsa          = $recentMsa          ?? [];
$recentProjects     = $recentProjects     ?? [];
$outOfControl = (int) ($outOfControl ?? 0);
$avgCpk = $avgCpk ?? null;
$avgGrr = $avgGrr ?? null;

$stats = array_merge([
    'projects'   => 0,
    'datasets'   => 0,
    'charts'     => 0,
    'capability' => 0,
    'msa'        => 0,
], $stats);

// 🎯 انواع نمودار صفتی
$attributeTypes = ['p', 'np', 'c', 'u'];
?>
<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex-between qc-mb-3">
        <h2>
            <i class="fas fa-chart-bar"></i>
            داشبورد کنترل کیفیت
            <small class="qc-text-muted" style="font-size:.75rem;font-weight:500;">
                — <?= htmlspecialchars($system['company_name'] ?? '') ?>
            </small>
        </h2>
        <div class="qc-flex qc-gap-1">
            <a href="<?= CURRENT_MODULE_URL ?>?controller=dataset&action=create" class="btn-qc-primary">
                <i class="fas fa-plus"></i> دیتاست جدید
            </a>
            <a href="<?= CURRENT_MODULE_URL ?>?controller=project&action=create" class="btn-qc-outline">
                <i class="fas fa-folder-plus"></i> پروژه جدید
            </a>
        </div>
    </div>

    <div class="qc-stats-grid qc-mb-4">
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
    </div>

    <div class="qc-stats-grid qc-mb-4">
        <div class="qc-stat-card danger">
            <small><i class="fas fa-exclamation-triangle"></i> خارج از کنترل</small>
            <h3><?= $outOfControl ?></h3>
        </div>
        <div class="qc-stat-card success">
            <small><i class="fas fa-chart-area"></i> میانگین Cpk</small>
            <h3><?= $avgCpk !== null ? number_format((float) $avgCpk, 2) : '—' ?></h3>
        </div>
        <div class="qc-stat-card info">
            <small><i class="fas fa-ruler-combined"></i> میانگین %GRR</small>
            <h3><?= $avgGrr !== null ? number_format((float) $avgGrr, 2) . '%' : '—' ?></h3>
        </div>
    </div>

    <div class="qc-main-grid">
        <div class="qc-flex-col qc-gap-2">

            <div class="qc-card">
                <div class="qc-card-header">
                    <h3 class="qc-card-title"><i class="fas fa-folder"></i> آخرین پروژه‌ها</h3>
                    <a href="<?= CURRENT_MODULE_URL ?>?controller=project" class="btn-qc-outline btn-sm">مشاهده همه</a>
                </div>
                <div class="qc-card-body" style="padding:0;">
                    <?php if (empty($recentProjects)): ?>
                        <div class="qc-empty-state">
                            <i class="fas fa-folder-open"></i>
                            <h4>هنوز پروژه‌ای ثبت نشده</h4>
                            <a href="<?= CURRENT_MODULE_URL ?>?controller=project&action=create" class="btn-qc-primary btn-sm">
                                <i class="fas fa-plus"></i> ایجاد پروژه
                            </a>
                        </div>
                    <?php else: ?>
                        <table class="qc-table">
                            <thead>
                                <tr>
                                    <th>نام</th>
                                    <th>محصول</th>
                                    <th>CTQ</th>
                                    <th>دیتاست</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentProjects as $p): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= CURRENT_MODULE_URL ?>?controller=project&action=show&id=<?= (int)$p['id'] ?>">
                                                <?= htmlspecialchars($p['name']) ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($p['product_name'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($p['ctq'] ?? '—') ?></td>
                                        <td><?= (int)($p['dataset_count'] ?? 0) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div class="qc-card">
                <div class="qc-card-header">
                    <h3 class="qc-card-title"><i class="fas fa-chart-line"></i> آخرین نمودارهای کنترل</h3>
                    <a href="<?= CURRENT_MODULE_URL ?>?controller=control_chart" class="btn-qc-outline btn-sm">مشاهده همه</a>
                </div>
                <div class="qc-card-body" style="padding:0;">
                    <?php if (empty($recentCharts)): ?>
                        <div class="qc-empty-state">
                            <i class="fas fa-chart-line"></i>
                            <h4>هنوز نموداری محاسبه نشده</h4>
                        </div>
                    <?php else: ?>
                        <table class="qc-table">
                            <thead>
                                <tr>
                                    <th>دیتاست</th>
                                    <th>نوع</th>
                                    <th>وضعیت</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentCharts as $c): ?>
                                    <?php
                                    // 🎯 لینک هوشمند بر اساس نوع نمودار
                                    $ct = $c['chart_type'] ?? '';
                                    $isAttr = in_array($ct, $attributeTypes, true);
                                    $link = $isAttr ? 'attribute_chart' : 'control_chart';
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="<?= CURRENT_MODULE_URL ?>?controller=<?= $link ?>&action=show&id=<?= (int)$c['id'] ?>">
                                                <?= htmlspecialchars($c['dataset_name'] ?? '—') ?>
                                            </a>
                                        </td>
                                        <td><code><?= htmlspecialchars($ct) ?></code></td>
                                        <td>
                                            <?php if ((int)$c['in_control'] === 1): ?>
                                                <span class="qc-status-badge qc-status-active">
                                                    <i class="fas fa-check"></i> در کنترل
                                                </span>
                                            <?php else: ?>
                                                <span class="qc-status-badge qc-status-danger">
                                                    <i class="fas fa-times"></i> خارج کنترل
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <div class="qc-flex-col qc-gap-2">

            <div class="qc-card">
                <div class="qc-card-header">
                    <h3 class="qc-card-title"><i class="fas fa-bullseye"></i> تحلیل‌های قابلیت</h3>
                </div>
                <div class="qc-card-body" style="padding:0;">
                    <?php if (empty($recentCapabilities)): ?>
                        <div class="qc-empty-state">
                            <i class="fas fa-bullseye"></i>
                            <p>هنوز تحلیل قابلیتی انجام نشده</p>
                        </div>
                    <?php else: ?>
                        <table class="qc-table">
                            <thead>
                                <tr>
                                    <th>دیتاست</th>
                                    <th>Cpk</th>
                                    <th>وضعیت</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentCapabilities as $s): ?>
                                    <?php $cpk = $s['cpk'] !== null ? (float)$s['cpk'] : null; ?>
                                    <tr>
                                        <td>
                                            <a href="<?= CURRENT_MODULE_URL ?>?controller=capability&action=show&id=<?= (int)$s['id'] ?>">
                                                <?= htmlspecialchars($s['dataset_name'] ?? '—') ?>
                                            </a>
                                        </td>
                                        <td><?= $cpk !== null ? number_format($cpk, 2) : '—' ?></td>
                                        <td>
                                            <?php
                                            $cls = 'qc-status-inactive';
                                            if ($cpk !== null) {
                                                if ($cpk >= 1.33)     $cls = 'qc-status-active';
                                                elseif ($cpk >= 1.00) $cls = 'qc-status-warning';
                                                else                  $cls = 'qc-status-danger';
                                            }
                                            ?>
                                            <span class="qc-status-badge <?= $cls ?>">
                                                <?= htmlspecialchars(CapabilityStudy::verdictLabel($cpk)) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div class="qc-card">
                <div class="qc-card-header">
                    <h3 class="qc-card-title"><i class="fas fa-ruler-combined"></i> مطالعات MSA</h3>
                </div>
                <div class="qc-card-body" style="padding:0;">
                    <?php if (empty($recentMsa)): ?>
                        <div class="qc-empty-state">
                            <i class="fas fa-ruler-combined"></i>
                            <p>هنوز مطالعه MSA انجام نشده</p>
                        </div>
                    <?php else: ?>
                        <table class="qc-table">
                            <thead>
                                <tr>
                                    <th>نام</th>
                                    <th>%GRR</th>
                                    <th>وضعیت</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentMsa as $m): ?>
                                    <?php $grr = $m['pct_grr'] !== null ? (float)$m['pct_grr'] : null; ?>
                                    <tr>
                                        <td>
                                            <a href="<?= CURRENT_MODULE_URL ?>?controller=msa&action=show&id=<?= (int)$m['id'] ?>">
                                                <?= htmlspecialchars($m['name'] ?? '—') ?>
                                            </a>
                                        </td>
                                        <td><?= $grr !== null ? number_format($grr, 2) . '%' : '—' ?></td>
                                        <td>
                                            <?php
                                            $cls = 'qc-status-inactive';
                                            if ($grr !== null) {
                                                if ($grr < 10)        $cls = 'qc-status-active';
                                                elseif ($grr <= 30)   $cls = 'qc-status-warning';
                                                else                  $cls = 'qc-status-danger';
                                            }
                                            ?>
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

        </div>
    </div>

</div>

<script src="/public/assets/js/software/quality.js"></script>