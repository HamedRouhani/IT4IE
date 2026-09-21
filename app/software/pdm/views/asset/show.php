<?php
/**
 * PdM Analyzer - نمایش جزئیات دارایی
 * مسیر: app/software/pdm/views/asset/show.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <!-- هدر -->
    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-cog"></i> <?= pdm_e($asset['name']) ?>
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                کد: <code><?= pdm_e($asset['asset_code']) ?></code>
                <?php if (!empty($asset['location_name'])): ?>
                    — مکان: <strong><?= pdm_e($asset['location_name']) ?></strong>
                <?php endif; ?>
                <?php if (!empty($asset['category_name'])): ?>
                    — دسته: <strong><?= pdm_e($asset['category_name']) ?></strong>
                <?php endif; ?>
            </p>
        </div>
        <div class="pdm-flex pdm-gap-2">
            <a href="<?= pdm_url('asset', 'edit', ['id' => $asset['id']]) ?>" class="btn-pdm-outline">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <a href="<?= pdm_url('asset') ?>" class="btn-pdm-outline">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="pdm-alert <?= pdm_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= pdm_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- کارت‌های آماری -->
    <div class="stats-grid pdm-mb-4">
        <div class="pdm-stat-card">
            <small><i class="fas fa-clipboard-list"></i> دستورکارها</small>
            <h3><?= pdm_num($stats['total_work_orders']) ?></h3>
        </div>
        <div class="pdm-stat-card critical">
            <small><i class="fas fa-exclamation-triangle"></i> حالات خرابی</small>
            <h3><?= pdm_num($stats['total_failures']) ?></h3>
        </div>
        <div class="pdm-stat-card success">
            <small><i class="fas fa-puzzle-piece"></i> قطعات یدکی مرتبط</small>
            <h3><?= pdm_num($stats['total_spare_parts']) ?></h3>
        </div>
        <div class="pdm-stat-card warning">
            <small><i class="fas fa-shield-alt"></i> بحرانیت</small>
            <h3 style="font-size: 1.1rem;">
                <span class="pdm-criticality-badge <?= pdm_criticality_class($asset['criticality']) ?>">
                    <?= pdm_criticality_label($asset['criticality']) ?>
                </span>
            </h3>
        </div>
    </div>

    <!-- گرید اصلی -->
    <div class="main-grid">

        <!-- ستون چپ -->
        <div>
            <!-- مشخصات فنی -->
            <div class="card pdm-mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-microchip"></i>
                        مشخصات فنی
                    </h3>
                </div>
                <div class="card-body">
                    <table class="pdm-table">
                        <tbody>
                            <tr>
                                <th style="width: 180px;">سازنده</th>
                                <td><?= pdm_e($asset['manufacturer'] ?? '—') ?></td>
                            </tr>
                            <tr>
                                <th>مدل</th>
                                <td><?= pdm_e($asset['model'] ?? '—') ?></td>
                            </tr>
                            <tr>
                                <th>شماره سریال</th>
                                <td><?= pdm_e($asset['serial_number'] ?? '—') ?></td>
                            </tr>
                            <tr>
                                <th>تاریخ نصب</th>
                                <td>
                                    <?php if (!empty($asset['installation_date'])): ?>
                                        <?= pdm_e(\App\Helpers\DateHelper::toJalali($asset['installation_date'], 'Y/m/d')) ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>وضعیت</th>
                                <td>
                                    <span class="pdm-status-badge pdm-status-<?= pdm_e($asset['status']) ?>">
                                        <?= pdm_status_label($asset['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>تاریخ ایجاد</th>
                                <td><?= pdm_date($asset['created_at'], 'Y/m/d H:i') ?></td>
                            </tr>
                            <?php if (!empty($asset['description'])): ?>
                            <tr>
                                <th>توضیحات</th>
                                <td><?= nl2br(pdm_e($asset['description'])) ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- آخرین دستورکارها -->
            <div class="card pdm-mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clipboard-list"></i>
                        آخرین دستورکارها
                    </h3>
                    <a href="<?= pdm_url('workorder', 'create', ['asset_id' => $asset['id']]) ?>"
                       class="btn-pdm-primary btn-sm">
                        <i class="fas fa-plus"></i> دستورکار جدید
                    </a>
                </div>
                <?php if (empty($workOrders)): ?>
                    <div class="pdm-empty-state" style="padding: 2rem 1rem;">
                        <i class="fas fa-clipboard"></i>
                        <p>هنوز دستورکاری ثبت نشده است.</p>
                    </div>
                <?php else: ?>
                    <table class="pdm-table">
                        <thead>
                            <tr>
                                <th>شماره</th>
                                <th>عنوان</th>
                                <th>نوع</th>
                                <th>وضعیت</th>
                                <th>تاریخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($workOrders as $wo): ?>
                                <tr>
                                    <td><?= pdm_e($wo['wo_number'] ?? '—') ?></td>
                                    <td><?= pdm_e(pdm_truncate($wo['title'] ?? '—', 40)) ?></td>
                                    <td><?= pdm_e($wo['maintenance_type_name'] ?? '—') ?></td>
                                    <td>
                                        <span class="pdm-status-badge <?= pdm_wo_status_class($wo['status']) ?>">
                                            <?= pdm_wo_status_label($wo['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= pdm_date($wo['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- حالات خرابی -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        حالات خرابی
                    </h3>
                </div>
                <?php if (empty($failureModes)): ?>
                    <div class="pdm-empty-state" style="padding: 2rem 1rem;">
                        <i class="fas fa-shield-alt"></i>
                        <p>حالت خرابی ثبت نشده است.</p>
                    </div>
                <?php else: ?>
                    <table class="pdm-table">
                        <thead>
                            <tr>
                                <th>کد</th>
                                <th>نام</th>
                                <th>شدت</th>
                                <th>تکرار</th>
                                <th>تشخیص</th>
                                <th>RPN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($failureModes as $fm): ?>
                                <?php
                                $rpn = ((int) ($fm['severity'] ?? 0)) *
                                       ((int) ($fm['occurrence'] ?? 0)) *
                                       ((int) ($fm['detection'] ?? 0));
                                ?>
                                <tr>
                                    <td><code><?= pdm_e($fm['code'] ?? '—') ?></code></td>
                                    <td><?= pdm_e($fm['name'] ?? '—') ?></td>
                                    <td><?= pdm_num($fm['severity'] ?? 0) ?></td>
                                    <td><?= pdm_num($fm['occurrence'] ?? 0) ?></td>
                                    <td><?= pdm_num($fm['detection'] ?? 0) ?></td>
                                    <td>
                                        <span class="pdm-criticality-badge <?= $rpn > 100 ? 'pdm-criticality-critical' : ($rpn > 50 ? 'pdm-criticality-high' : 'pdm-criticality-medium') ?>">
                                            <?= pdm_num($rpn) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- ستون راست -->
        <div>
            <!-- قطعات یدکی مرتبط -->
            <div class="card pdm-mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-puzzle-piece"></i>
                        قطعات یدکی
                    </h3>
                </div>
                <?php if (empty($spareParts)): ?>
                    <div class="pdm-empty-state" style="padding: 2rem 1rem;">
                        <i class="fas fa-puzzle-piece"></i>
                        <p>قطعه‌ای متصل نشده است.</p>
                    </div>
                <?php else: ?>
                    <table class="pdm-table">
                        <thead>
                            <tr>
                                <th>کد</th>
                                <th>نام</th>
                                <th>موجودی</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($spareParts as $sp): ?>
                                <tr>
                                    <td><code><?= pdm_e($sp['spare_code'] ?? '—') ?></code></td>
                                    <td><?= pdm_e($sp['spare_name'] ?? '—') ?></td>
                                    <td><?= pdm_num($sp['stock_quantity'] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- اسناد -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-alt"></i>
                        اسناد و مدارک
                    </h3>
                </div>
                <?php if (empty($documents)): ?>
                    <div class="pdm-empty-state" style="padding: 2rem 1rem;">
                        <i class="fas fa-file"></i>
                        <p>سندی بارگذاری نشده است.</p>
                    </div>
                <?php else: ?>
                    <ul style="list-style: none; padding: 1rem;">
                        <?php foreach ($documents as $doc): ?>
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb;">
                                <i class="fas fa-file-pdf" style="color: var(--pdm-danger);"></i>
                                <a href="#" style="color: var(--pdm-primary-dark);">
                                    <?= pdm_e($doc['title'] ?? $doc['file_name'] ?? '—') ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<script src="/public/assets/js/software/pdm.js"></script>