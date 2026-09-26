<?php $systems = $systems ?? []; ?>
<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex-between qc-mb-3">
        <h2><i class="fas fa-building"></i> سیستم‌های کیفیت</h2>
        <a href="<?= CURRENT_MODULE_URL ?>?controller=system&action=create" class="btn-qc-primary">
            <i class="fas fa-plus"></i> سیستم جدید
        </a>
    </div>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="qc-alert success qc-mb-3">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="qc-alert danger qc-mb-3">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="qc-card">
        <div class="qc-card-body" style="padding:0;">
            <?php if (empty($systems)): ?>
                <div class="qc-empty-state">
                    <i class="fas fa-building"></i>
                    <h4>هنوز سیستم کیفیتی ثبت نشده</h4>
                    <p>برای شروع، اولین شرکت / واحد کیفیت خود را ایجاد کنید</p>
                    <a href="<?= CURRENT_MODULE_URL ?>?controller=system&action=create" class="btn-qc-primary">
                        <i class="fas fa-plus"></i> ایجاد اولین سیستم
                    </a>
                </div>
            <?php else: ?>
                <table class="qc-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام شرکت</th>
                            <th>صنعت</th>
                            <th>اندازه</th>
                            <th>وضعیت</th>
                            <th>تاریخ ایجاد</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sizes = [
                            'micro' => 'خیلی کوچک', 'small' => 'کوچک', 'medium' => 'متوسط',
                            'large' => 'بزرگ', 'enterprise' => 'سازمانی',
                        ];
                        foreach ($systems as $i => $s): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($s['company_name']) ?></td>
                                <td><?= htmlspecialchars($s['industry'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($sizes[$s['company_size']] ?? $s['company_size']) ?></td>
                                <td>
                                    <?php if ($s['status'] === 'active'): ?>
                                        <span class="qc-status-badge qc-status-active">فعال</span>
                                    <?php else: ?>
                                        <span class="qc-status-badge qc-status-inactive">آرشیو</span>
                                    <?php endif; ?>
                                </td>
                                <td class="qc-text-muted" style="font-size:.8rem;"><?= htmlspecialchars($s['created_at']) ?></td>
                                <td>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=system&action=edit&id=<?= (int)$s['id'] ?>"
                                       class="btn-qc-outline btn-sm">
                                        <i class="fas fa-edit"></i>
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