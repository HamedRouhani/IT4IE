<?php
/**
 * PdM Analyzer - چاپ گزارش دستورکارها
 * مسیر: app/software/pdm/views/report/print_work_orders.php
 */
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <title>گزارش دستورکارها</title>
    <link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">
</head>
<body class="pdm-print-body">

<div class="pdm-print-header">
    <h1>گزارش دستورکارها</h1>
    <p>تاریخ چاپ: <?= pdm_date(date('Y-m-d H:i:s'), 'Y/m/d H:i') ?></p>
</div>

<div class="pdm-print-info">
    <span>مجموع: <?= pdm_num(count($workOrders)) ?> دستورکار</span>
    <span>IT4IE - سیستم PdM</span>
</div>

<!-- دکمه‌ها -->
<div class="pdm-print-actions pdm-no-print">
    <button onclick="window.print()" class="pdm-print-btn">
        🖨️ چاپ گزارش
    </button>
    <button onclick="window.close()" class="pdm-close-btn">
        ✖ بستن
    </button>
</div>

<?php if (empty($workOrders)): ?>
    <p class="pdm-print-empty">هیچ دستورکاری یافت نشد.</p>
<?php else: ?>
    <table class="pdm-table">
        <thead>
            <tr>
                <th>#</th>
                <th>شماره</th>
                <th>عنوان</th>
                <th>دارایی</th>
                <th>نوع</th>
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
                    <td><?= pdm_e($wo['title']) ?></td>
                    <td>
                        <?= pdm_e($wo['asset_name'] ?? '—') ?>
                        <?php if (!empty($wo['asset_code'])): ?>
                            (<?= pdm_e($wo['asset_code']) ?>)
                        <?php endif; ?>
                    </td>
                    <td><?= pdm_e($wo['maintenance_type_name'] ?? '—') ?></td>
                    <td><?= pdm_wo_status_label($wo['status']) ?></td>
                    <td><?= $wo['planned_date'] ? pdm_date($wo['planned_date'], 'Y/m/d') : '—' ?></td>
                    <td><?= pdm_date($wo['created_at'], 'Y/m/d') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>