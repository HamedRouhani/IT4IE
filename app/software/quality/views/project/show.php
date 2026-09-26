<?php
use App\Software\Quality\Models\Dataset;

$project  = $project  ?? [];
$datasets = $datasets ?? [];
?>
<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex-between qc-mb-3">
        <h2>
            <i class="fas fa-folder-open"></i>
            <?= htmlspecialchars($project['name'] ?? '') ?>
        </h2>
        <div class="qc-flex qc-gap-1">
            <a href="<?= CURRENT_MODULE_URL ?>?controller=dataset&action=create&project_id=<?= (int)$project['id'] ?>"
               class="btn-qc-primary">
                <i class="fas fa-plus"></i> دیتاست جدید
            </a>
            <a href="<?= CURRENT_MODULE_URL ?>?controller=project&action=edit&id=<?= (int)$project['id'] ?>"
               class="btn-qc-outline">
                <i class="fas fa-edit"></i> ویرایش
            </a>
        </div>
    </div>

    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-info-circle"></i> اطلاعات پروژه</h3>
        </div>
        <div class="qc-card-body">
            <div class="qc-chart-summary">
                <div class="qc-summary-item">
                    <small>محصول</small>
                    <strong><?= htmlspecialchars($project['product_name'] ?? '—') ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>فرآیند</small>
                    <strong><?= htmlspecialchars($project['process_name'] ?? '—') ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>CTQ</small>
                    <strong><?= htmlspecialchars($project['ctq'] ?? '—') ?></strong>
                </div>
                <div class="qc-summary-item">
                    <small>واحد</small>
                    <strong><?= htmlspecialchars($project['unit'] ?? '—') ?></strong>
                </div>
            </div>
            <?php if (!empty($project['description'])): ?>
                <p class="qc-text-muted qc-mt-3" style="font-size:.88rem;line-height:1.8;">
                    <?= nl2br(htmlspecialchars($project['description'])) ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="qc-card">
        <div class="qc-card-header">
            <h3 class="qc-card-title"><i class="fas fa-database"></i> دیتاست‌ها</h3>
        </div>
        <div class="qc-card-body" style="padding:0;">
            <?php if (empty($datasets)): ?>
                <div class="qc-empty-state">
                    <i class="fas fa-database"></i>
                    <h4>هنوز دیتاستی ثبت نشده</h4>
                    <a href="<?= CURRENT_MODULE_URL ?>?controller=dataset&action=create&project_id=<?= (int)$project['id'] ?>"
                       class="btn-qc-primary">
                        <i class="fas fa-plus"></i> ایجاد اولین دیتاست
                    </a>
                </div>
            <?php else: ?>
                <table class="qc-table">
                    <thead>
                        <tr>
                            <th>نام</th>
                            <th>نوع نمودار</th>
                            <th>LSL</th>
                            <th>USL</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($datasets as $d): ?>
                            <tr>
                                <td><?= htmlspecialchars($d['name']) ?></td>
                                <td>
                                    <span class="qc-chart-badge">
                                        <?= htmlspecialchars(Dataset::CHART_TYPES[$d['chart_type']] ?? $d['chart_type']) ?>
                                    </span>
                                </td>
                                <td><?= $d['spec_lsl'] !== null ? number_format((float)$d['spec_lsl'], 3) : '—' ?></td>
                                <td><?= $d['spec_usl'] !== null ? number_format((float)$d['spec_usl'], 3) : '—' ?></td>
                                <td>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=dataset&action=data&id=<?= (int)$d['id'] ?>"
                                       class="btn-qc-outline btn-sm">
                                        <i class="fas fa-database"></i> داده‌ها
                                    </a>
                                    <?php
                                    $isAttr = in_array($d['chart_type'], ['p', 'np', 'c', 'u'], true);
                                    $cc = $isAttr ? 'attribute_chart' : 'control_chart';
                                    ?>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=<?= $cc ?>&action=compute&id=<?= (int)$d['id'] ?>"
                                    class="btn-qc-primary btn-sm">
                                        <i class="fas fa-chart-line"></i> محاسبه
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