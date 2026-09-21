<?php
/**
 * PdM Analyzer - چاپ گزارش خرابی‌ها
 * مسیر: app/software/pdm/views/report/print_failures.php
 */
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <title>گزارش خرابی‌ها (Pareto)</title>
    <link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">
</head>
<body class="pdm-print-body">

<div class="pdm-print-header">
    <h1>گزارش خرابی‌ها (Pareto)</h1>
    <p>تاریخ چاپ: <?= pdm_date(date('Y-m-d H:i:s'), 'Y/m/d H:i') ?></p>
</div>

<div class="pdm-print-info">
    <span>مجموع: <?= pdm_num(count($failureModes)) ?> حالت خرابی</span>
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

<?php if (empty($failureModes)): ?>
    <p class="pdm-print-empty">هیچ حالت خرابی ثبت نشده است.</p>
<?php else: ?>
    <table class="pdm-table">
        <thead>
            <tr>
                <th>#</th>
                <th>کد</th>
                <th>نام</th>
                <th>دارایی</th>
                <th>S</th>
                <th>O</th>
                <th>D</th>
                <th>RPN</th>
                <th>تجمعی</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($failureModes as $i => $fm): ?>
                <tr>
                    <td><?= pdm_num($i + 1) ?></td>
                    <td><?= pdm_e($fm['code'] ?? '—') ?></td>
                    <td><?= pdm_e($fm['name']) ?></td>
                    <td><?= pdm_e($fm['asset_name'] ?? '—') ?></td>
                    <td><?= pdm_num($fm['severity']) ?></td>
                    <td><?= pdm_num($fm['occurrence']) ?></td>
                    <td><?= pdm_num($fm['detection']) ?></td>
                    <td><strong><?= pdm_num($fm['rpn']) ?></strong></td>
                    <td><?= pdm_num($fm['cumulative_percent']) ?>٪</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>