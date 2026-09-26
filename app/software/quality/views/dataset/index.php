<?php
use App\Software\Quality\Models\Dataset;

$datasets = $datasets ?? [];
$system   = $system   ?? [];
?>
<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex-between qc-mb-3">
        <h2>
            <i class="fas fa-database"></i> دیتاست‌های کیفیت
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
            <?php if (empty($datasets)): ?>
                <div class="qc-empty-state">
                    <i class="fas fa-database"></i>
                    <h4>هنوز دیتاستی ثبت نشده</h4>
                    <p>برای شروع تحلیل SPC، اولین دیتاست خود را ایجاد کنید</p>
                    <a href="<?= CURRENT_MODULE_URL ?>?controller=dataset&action=create" class="btn-qc-primary">
                        <i class="fas fa-plus"></i> ایجاد اولین دیتاست
                    </a>
                </div>
            <?php else: ?>
                <table class="qc-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام</th>
                            <th>نوع نمودار</th>
                            <th>LSL</th>
                            <th>USL</th>
                            <th>Target</th>
                            <th>حجم زیرگروه</th>
                            <th>تاریخ</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($datasets as $i => $d): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($d['name']) ?></td>
                                <td>
                                    <span class="qc-chart-badge">
                                        <?= htmlspecialchars(Dataset::CHART_TYPES[$d['chart_type']] ?? $d['chart_type']) ?>
                                    </span>
                                </td>
                                <td><?= $d['spec_lsl']    !== null ? number_format((float)$d['spec_lsl'], 3)    : '—' ?></td>
                                <td><?= $d['spec_usl']    !== null ? number_format((float)$d['spec_usl'], 3)    : '—' ?></td>
                                <td><?= $d['spec_target'] !== null ? number_format((float)$d['spec_target'], 3) : '—' ?></td>
                                <td><?= $d['subgroup_size'] !== null ? (int)$d['subgroup_size'] : '—' ?></td>
                                <td class="qc-text-muted" style="font-size:.78rem;">
                                    <?= htmlspecialchars($d['created_at'] ?? '') ?>
                                </td>
                                <td>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=dataset&action=data&id=<?= (int)$d['id'] ?>"
                                       class="btn-qc-outline btn-sm" title="داده‌ها">
                                        <i class="fas fa-database"></i>
                                    </a>
                                    <?php
                                    $isAttr = in_array($d['chart_type'], ['p', 'np', 'c', 'u'], true);
                                    $cc = $isAttr ? 'attribute_chart' : 'control_chart';
                                    ?>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=<?= $cc ?>&action=compute&id=<?= (int)$d['id'] ?>"
                                    class="btn-qc-primary btn-sm" title="محاسبه نمودار">
                                        <i class="fas fa-chart-line"></i>
                                    </a>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=capability&action=compute&id=<?= (int)$d['id'] ?>"
                                       class="btn-qc-success btn-sm" title="تحلیل قابلیت">
                                        <i class="fas fa-bullseye"></i>
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