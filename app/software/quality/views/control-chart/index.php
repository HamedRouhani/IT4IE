<?php
$charts = $charts ?? [];
$system = $system ?? [];
$isAttribute = $isAttribute ?? false;

$pageTitle   = $isAttribute ? 'نمودارهای صفتی' : 'نمودارهای کنترل (متغیر)';
$pageIcon    = $isAttribute ? 'fas fa-chart-bar' : 'fas fa-chart-line';
$controller  = $isAttribute ? 'attribute_chart' : 'control_chart';
$emptyText   = $isAttribute
    ? 'هنوز نمودار صفتی محاسبه نشده — از دیتاست‌های p, np, c, u استفاده کنید'
    : 'هنوز نمودار متغیر محاسبه نشده — از دیتاست‌های X̄-R, X̄-S, I-MR استفاده کنید';
?>
<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex-between qc-mb-3">
        <h2>
            <i class="<?= $pageIcon ?>"></i> <?= $pageTitle ?>
            <small class="qc-text-muted" style="font-size:.75rem;font-weight:500;">
                — <?= htmlspecialchars($system['company_name'] ?? '') ?>
            </small>
        </h2>
        <a href="<?= CURRENT_MODULE_URL ?>?controller=dataset&action=create" class="btn-qc-primary">
            <i class="fas fa-plus"></i> دیتاست جدید
        </a>
    </div>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="qc-alert success qc-mb-3">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <div class="qc-card">
        <div class="qc-card-body" style="padding:0;">
            <?php if (empty($charts)): ?>
                <div class="qc-empty-state">
                    <i class="<?= $pageIcon ?>"></i>
                    <h4>هنوز نموداری محاسبه نشده</h4>
                    <p><?= $emptyText ?></p>
                    <a href="<?= CURRENT_MODULE_URL ?>?controller=dataset&action=create" class="btn-qc-primary">
                        <i class="fas fa-plus"></i> ایجاد دیتاست
                    </a>
                </div>
            <?php else: ?>
                <table class="qc-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>دیتاست</th>
                            <th>پروژه</th>
                            <th>نوع</th>
                            <th>CL</th>
                            <th>UCL</th>
                            <th>LCL</th>
                            <th>σ̂</th>
                            <th>وضعیت</th>
                            <th>تاریخ</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($charts as $i => $c): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($c['dataset_name'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($c['project_name'] ?? '—') ?></td>
                                <td><code><?= htmlspecialchars($c['chart_type']) ?></code></td>
                                <td><?= $c['center_line'] !== null ? number_format((float)$c['center_line'], 4) : '—' ?></td>
                                <td><?= $c['ucl'] !== null ? number_format((float)$c['ucl'], 4) : '—' ?></td>
                                <td><?= $c['lcl'] !== null ? number_format((float)$c['lcl'], 4) : '—' ?></td>
                                <td><?= $c['sigma_hat'] !== null ? number_format((float)$c['sigma_hat'], 4) : '—' ?></td>
                                <td>
                                    <?php if ((int)$c['in_control'] === 1): ?>
                                        <span class="qc-status-badge qc-status-active">
                                            <i class="fas fa-check"></i> در کنترل
                                        </span>
                                    <?php else: ?>
                                        <span class="qc-status-badge qc-status-danger">
                                            <i class="fas fa-exclamation-triangle"></i> خارج کنترل
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="qc-text-muted" style="font-size:.78rem;">
                                    <?= htmlspecialchars($c['computed_at'] ?? '') ?>
                                </td>
                                <td>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=<?= $controller ?>&action=show&id=<?= (int)$c['id'] ?>"
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