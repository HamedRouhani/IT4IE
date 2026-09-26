<?php
use App\Software\Quality\Models\MsaStudy;

$studies = $studies ?? [];
$system  = $system  ?? [];
$avgGrr  = $avgGrr  ?? null;
?>
<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex-between qc-mb-3">
        <h2>
            <i class="fas fa-ruler-combined"></i> مطالعات MSA (Gage R&R)
            <small class="qc-text-muted" style="font-size:.75rem;font-weight:500;">
                — <?= htmlspecialchars($system['company_name'] ?? '') ?>
            </small>
        </h2>
        <a href="<?= CURRENT_MODULE_URL ?>?controller=msa&action=create" class="btn-qc-primary">
            <i class="fas fa-plus"></i> مطالعه جدید
        </a>
    </div>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="qc-alert success qc-mb-3">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if ($avgGrr !== null): ?>
        <div class="qc-stats-grid qc-mb-3">
            <div class="qc-stat-card <?= $avgGrr < 10 ? 'success' : ($avgGrr <= 30 ? 'warning' : 'danger') ?>">
                <small><i class="fas fa-ruler-combined"></i> میانگین %GRR</small>
                <h3><?= number_format((float)$avgGrr, 2) ?>%</h3>
            </div>
        </div>
    <?php endif; ?>

    <div class="qc-card">
        <div class="qc-card-body" style="padding:0;">
            <?php if (empty($studies)): ?>
                <div class="qc-empty-state">
                    <i class="fas fa-ruler-combined"></i>
                    <h4>هنوز مطالعه MSA انجام نشده</h4>
                    <p>برای ارزیابی سیستم اندازه‌گیری، اولین مطالعه Gage R&R خود را ایجاد کنید</p>
                    <a href="<?= CURRENT_MODULE_URL ?>?controller=msa&action=create" class="btn-qc-primary">
                        <i class="fas fa-plus"></i> ایجاد اولین مطالعه
                    </a>
                </div>
            <?php else: ?>
                <table class="qc-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام مطالعه</th>
                            <th>پروژه</th>
                            <th>روش</th>
                            <th>قطعات</th>
                            <th>اپراتورها</th>
                            <th>تکرار</th>
                            <th>%GRR</th>
                            <th>ndc</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($studies as $i => $s): ?>
                            <?php
                            $grr = $s['pct_grr'] !== null ? (float)$s['pct_grr'] : null;
                            $cls = 'qc-status-inactive';
                            if ($grr !== null) {
                                if ($grr < 10)        $cls = 'qc-status-active';
                                elseif ($grr <= 30)   $cls = 'qc-status-warning';
                                else                  $cls = 'qc-status-danger';
                            }
                            ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=msa&action=show&id=<?= (int)$s['id'] ?>">
                                        <?= htmlspecialchars($s['name'] ?? '—') ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($s['project_name'] ?? '—') ?></td>
                                <td><code><?= htmlspecialchars($s['method'] ?? '') ?></code></td>
                                <td><?= (int)($s['num_parts'] ?? 0) ?></td>
                                <td><?= (int)($s['num_operators'] ?? 0) ?></td>
                                <td><?= (int)($s['num_trials'] ?? 0) ?></td>
                                <td><strong><?= $grr !== null ? number_format($grr, 2) . '%' : '—' ?></strong></td>
                                <td><?= (int)($s['ndc'] ?? 0) ?></td>
                                <td>
                                    <span class="qc-status-badge <?= $cls ?>">
                                        <?= htmlspecialchars(MsaStudy::verdictLabel($grr)) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=msa&action=show&id=<?= (int)$s['id'] ?>"
                                       class="btn-qc-outline btn-sm" title="نمایش">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=msa&action=delete&id=<?= (int)$s['id'] ?>"
                                       class="btn-qc-danger btn-sm qc-confirm-delete"
                                       data-message="آیا از حذف مطالعه «<?= htmlspecialchars($s['name']) ?>» اطمینان دارید؟"
                                       title="حذف">
                                        <i class="fas fa-trash"></i>
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