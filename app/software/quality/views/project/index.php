<?php
$projects = $projects ?? [];
$system   = $system   ?? [];
?>
<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex-between qc-mb-3">
        <h2>
            <i class="fas fa-folder"></i> پروژه‌های کیفیت
            <small class="qc-text-muted" style="font-size:.75rem;font-weight:500;">
                — <?= htmlspecialchars($system['company_name'] ?? '') ?>
            </small>
        </h2>
        <a href="<?= CURRENT_MODULE_URL ?>?controller=project&action=create" class="btn-qc-primary">
            <i class="fas fa-plus"></i> پروژه جدید
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
            <?php if (empty($projects)): ?>
                <div class="qc-empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h4>هنوز پروژه‌ای ثبت نشده</h4>
                    <p>اولین پروژه کنترل کیفیت خود را ایجاد کنید</p>
                    <a href="<?= CURRENT_MODULE_URL ?>?controller=project&action=create" class="btn-qc-primary">
                        <i class="fas fa-plus"></i> ایجاد اولین پروژه
                    </a>
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
                            <th>واحد</th>
                            <th>دیتاست</th>
                            <th>عملیات</th>
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
                                <td><?= htmlspecialchars($p['unit'] ?? '—') ?></td>
                                <td>
                                    <span class="qc-status-badge qc-status-info">
                                        <?= (int)($p['dataset_count'] ?? 0) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=project&action=edit&id=<?= (int)$p['id'] ?>"
                                       class="btn-qc-outline btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=dataset&action=create&project_id=<?= (int)$p['id'] ?>"
                                       class="btn-qc-primary btn-sm">
                                        <i class="fas fa-plus"></i>
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