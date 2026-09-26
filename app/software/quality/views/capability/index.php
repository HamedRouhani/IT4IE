<?php
use App\Software\Quality\Models\CapabilityStudy;

$studies = $studies ?? [];
$system  = $system  ?? [];
$avgCpk  = $avgCpk  ?? null;
?>
<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex-between qc-mb-3">
        <h2>
            <i class="fas fa-bullseye"></i> تحلیل قابلیت فرآیند
            <small class="qc-text-muted" style="font-size:.75rem;font-weight:500;">
                — <?= htmlspecialchars($system['company_name'] ?? '') ?>
            </small>
        </h2>
        <a href="<?= CURRENT_MODULE_URL ?>?controller=dataset" class="btn-qc-primary">
            <i class="fas fa-chart-line"></i> انتخاب دیتاست
        </a>
    </div>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="qc-alert success qc-mb-3">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if ($avgCpk !== null): ?>
        <div class="qc-stats-grid qc-mb-3">
            <div class="qc-stat-card <?= $avgCpk >= 1.33 ? 'success' : ($avgCpk >= 1.00 ? 'warning' : 'danger') ?>">
                <small><i class="fas fa-chart-area"></i> میانگین Cpk</small>
                <h3><?= number_format((float)$avgCpk, 2) ?></h3>
            </div>
        </div>
    <?php endif; ?>

    <div class="qc-card">
        <div class="qc-card-body" style="padding:0;">
            <?php if (empty($studies)): ?>
                <div class="qc-empty-state">
                    <i class="fas fa-bullseye"></i>
                    <h4>هنوز تحلیل قابلیتی انجام نشده</h4>
                    <p>برای شروع، از یک دیتاست با LSL/USL مشخص، تحلیل قابلیت بگیرید</p>
                    <a href="<?= CURRENT_MODULE_URL ?>?controller=dataset" class="btn-qc-primary">
                        <i class="fas fa-database"></i> انتخاب دیتاست
                    </a>
                </div>
            <?php else: ?>
                <table class="qc-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>دیتاست</th>
                            <th>پروژه</th>
                            <th>LSL</th>
                            <th>USL</th>
                            <th>Mean</th>
                            <th>Cp</th>
                            <th>Cpk</th>
                            <th>Pp</th>
                            <th>Ppk</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($studies as $i => $s): ?>
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
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($s['dataset_name'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($s['project_name'] ?? '—') ?></td>
                                <td><?= $s['lsl'] !== null ? number_format((float)$s['lsl'], 3) : '—' ?></td>
                                <td><?= $s['usl'] !== null ? number_format((float)$s['usl'], 3) : '—' ?></td>
                                <td><?= $s['mean'] !== null ? number_format((float)$s['mean'], 4) : '—' ?></td>
                                <td><?= $s['cp']  !== null ? number_format((float)$s['cp'], 3)  : '—' ?></td>
                                <td><strong><?= $cpk !== null ? number_format($cpk, 3) : '—' ?></strong></td>
                                <td><?= $s['pp']  !== null ? number_format((float)$s['pp'], 3)  : '—' ?></td>
                                <td><?= $s['ppk'] !== null ? number_format((float)$s['ppk'], 3) : '—' ?></td>
                                <td>
                                    <span class="qc-status-badge <?= $cls ?>">
                                        <?= htmlspecialchars(CapabilityStudy::verdictLabel($cpk)) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=capability&action=show&id=<?= (int)$s['id'] ?>"
                                       class="btn-qc-outline btn-sm">
                                        <i class="fas fa-eye"></i> نمایش
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/quality.js"></script>