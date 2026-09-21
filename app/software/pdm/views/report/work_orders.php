<?php
/**
 * PdM Analyzer - گزارش دستورکارها
 * مسیر: app/software/pdm/views/report/work_orders.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-clipboard-list"></i> گزارش دستورکارها
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                مجموع: <strong><?= pdm_num($summary['total']) ?></strong> دستورکار
            </p>
        </div>
        <div class="pdm-flex pdm-gap-2">
            <a href="<?= pdm_url('report', 'printWorkOrders', array_filter([
                'date_from' => $filters['date_from'],
                'date_to' => $filters['date_to'],
                'status' => $filters['status'],
                'asset_id' => $filters['asset_id'],
            ])) ?>" target="_blank" class="btn-pdm-primary">
                <i class="fas fa-print"></i> چاپ گزارش
            </a>
            <a href="<?= pdm_url('report') ?>" class="btn-pdm-outline">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>

    <!-- فیلترها -->
    <div class="card pdm-mb-3 pdm-no-print">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i>
                فیلترها
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= pdm_url('report', 'workOrders') ?>">
                <input type="hidden" name="controller" value="report">
                <input type="hidden" name="action" value="workOrders">

                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label">از تاریخ (شمسی)</label>
                        <input type="text" name="date_from" class="pdm-form-control pdm-datepicker"
                               value="<?= pdm_e($filters['date_from']) ?>"
                               placeholder="1404/01/01"
                               maxlength="10" autocomplete="off">
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label">تا تاریخ (شمسی)</label>
                        <input type="text" name="date_to" class="pdm-form-control pdm-datepicker"
                               value="<?= pdm_e($filters['date_to']) ?>"
                               placeholder="1404/12/29"
                               maxlength="10" autocomplete="off">
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label">وضعیت</label>
                        <select name="status" class="pdm-form-control">
                            <option value="">— همه —</option>
                            <option value="open" <?= $filters['status'] === 'open' ? 'selected' : '' ?>>باز</option>
                            <option value="in_progress" <?= $filters['status'] === 'in_progress' ? 'selected' : '' ?>>در حال انجام</option>
                            <option value="completed" <?= $filters['status'] === 'completed' ? 'selected' : '' ?>>تکمیل شده</option>
                            <option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>لغو شده</option>
                        </select>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label">دارایی</label>
                        <select name="asset_id" class="pdm-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($pdm_assets as $a): ?>
                                <option value="<?= (int) $a['id'] ?>"
                                    <?= (int) $filters['asset_id'] === (int) $a['id'] ? 'selected' : '' ?>>
                                    <?= pdm_e($a['asset_code']) ?> — <?= pdm_e($a['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="pdm-flex pdm-gap-2">
                    <button type="submit" class="btn-pdm-primary">
                        <i class="fas fa-search"></i> اعمال فیلتر
                    </button>
                    <a href="<?= pdm_url('report', 'workOrders') ?>" class="btn-pdm-outline">
                        <i class="fas fa-redo"></i> پاک کردن
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- آمار خلاصه -->
    <div class="stats-grid pdm-mb-3">
        <div class="pdm-stat-card"><small>باز</small><h3><?= pdm_num($summary['open']) ?></h3></div>
        <div class="pdm-stat-card warning"><small>در حال انجام</small><h3><?= pdm_num($summary['in_progress']) ?></h3></div>
        <div class="pdm-stat-card success"><small>تکمیل شده</small><h3><?= pdm_num($summary['completed']) ?></h3></div>
        <div class="pdm-stat-card critical"><small>لغو شده</small><h3><?= pdm_num($summary['cancelled']) ?></h3></div>
    </div>

    <!-- جدول -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                لیست دستورکارها
            </h3>
        </div>

        <?php if (empty($workOrders)): ?>
            <div class="pdm-empty-state">
                <i class="fas fa-clipboard"></i>
                <h4>هیچ دستورکاری یافت نشد</h4>
                <p>فیلترها را تغییر دهید.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="pdm-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>شماره</th>
                            <th>عنوان</th>
                            <th>دارایی</th>
                            <th>نوع</th>
                            <th>اولویت</th>
                            <th>وضعیت</th>
                            <th>تاریخ برنامه</th>
                            <th>تاریخ ایجاد</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($workOrders as $i => $wo): ?>
                            <tr>
                                <td><?= pdm_num($i + 1) ?></td>
                                <td><code><?= pdm_e($wo['wo_number']) ?></code></td>
                                <td><?= pdm_e(pdm_truncate($wo['title'], 40)) ?></td>
                                <td>
                                    <?= pdm_e($wo['asset_name'] ?? '—') ?>
                                    <?php if (!empty($wo['asset_code'])): ?>
                                        <br><small><code><?= pdm_e($wo['asset_code']) ?></code></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= pdm_e($wo['maintenance_type_name'] ?? '—') ?></td>
                                <td>
                                    <span class="pdm-criticality-badge <?= pdm_wo_priority_class($wo['priority']) ?>">
                                        <?= pdm_wo_priority_label($wo['priority']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="pdm-status-badge <?= pdm_wo_status_class($wo['status']) ?>">
                                        <?= pdm_wo_status_label($wo['status']) ?>
                                    </span>
                                </td>
                                <td><?= $wo['planned_date'] ? pdm_date($wo['planned_date'], 'Y/m/d') : '—' ?></td>
                                <td><?= pdm_date($wo['created_at'], 'Y/m/d') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<script src="/public/assets/js/software/pdm-datepicker.js?v=<?= time() ?>"></script>
<script src="/public/assets/js/software/pdm.js"></script>