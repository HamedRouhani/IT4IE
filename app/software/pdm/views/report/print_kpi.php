<?php
/**
 * PdM Analyzer - چاپ گزارش KPIها
 * مسیر: app/software/pdm/views/report/print_kpi.php
 */
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <title>گزارش شاخص‌های کلیدی (KPI)</title>
    <link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">
</head>
<body class="pdm-print-body">

<div class="pdm-print-header">
    <h1>گزارش شاخص‌های کلیدی (KPI)</h1>
    <p>تاریخ چاپ: <?= pdm_date(date('Y-m-d H:i:s'), 'Y/m/d H:i') ?></p>
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

<!-- KPIهای کلیدی -->
<div class="pdm-print-kpi-grid">
    <div class="pdm-print-kpi-box">
        <small>MTTR — میانگین زمان تعمیر (ساعت)</small>
        <h3><?= $kpiValues['MTTR'] !== null ? pdm_num(number_format($kpiValues['MTTR'], 2)) : '—' ?></h3>
    </div>
    <div class="pdm-print-kpi-box">
        <small>MTBF — میانگین زمان بین خرابی‌ها (ساعت)</small>
        <h3><?= $kpiValues['MTBF'] !== null ? pdm_num(number_format($kpiValues['MTBF'], 2)) : '—' ?></h3>
    </div>
    <div class="pdm-print-kpi-box">
        <small>دسترس‌پذیری</small>
        <h3><?= $kpiValues['Availability'] !== null ? pdm_num($kpiValues['Availability']) . '٪' : '—' ?></h3>
    </div>
    <div class="pdm-print-kpi-box">
        <small>قابلیت اطمینان (۱۰۰ ساعت)</small>
        <h3><?= $kpiValues['Reliability'] !== null ? pdm_num($kpiValues['Reliability']) . '٪' : '—' ?></h3>
    </div>
</div>

<!-- جدول تعاریف -->
<h3 class="pdm-print-section-title">📊 جدول شاخص‌ها</h3>
<table class="pdm-table">
    <thead>
        <tr>
            <th>کد</th>
            <th>نام</th>
            <th>فرمول</th>
            <th>واحد</th>
            <th>مقدار فعلی</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($kpiDefinitions as $kpi): ?>
            <?php $val = $kpiValues[$kpi['code']] ?? null; ?>
            <tr>
                <td><code><?= pdm_e($kpi['code']) ?></code></td>
                <td><?= pdm_e($kpi['name']) ?></td>
                <td><small><?= pdm_e($kpi['formula'] ?? '—') ?></small></td>
                <td><?= pdm_e($kpi['unit'] ?? '—') ?></td>
                <td>
                    <?php if ($val !== null): ?>
                        <strong style="color: var(--pdm-primary);"><?= pdm_num(number_format($val, 2)) ?></strong>
                    <?php else: ?>
                        <span style="color: #999;">—</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- آمار پایه -->
<h3 class="pdm-print-section-title">📈 آمار پایه سیستم</h3>
<table class="pdm-table">
    <tbody>
        <tr>
            <th style="width: 250px;">تعداد کل دارایی‌ها</th>
            <td><?= pdm_num($baseStats['total_assets']) ?></td>
        </tr>
        <tr>
            <th>تعداد کل دستورکارها</th>
            <td><?= pdm_num($baseStats['total_work_orders']) ?></td>
        </tr>
        <tr>
            <th>دستورکارهای تکمیل‌شده</th>
            <td><?= pdm_num($baseStats['completed_wos']) ?></td>
        </tr>
        <tr>
            <th>دستورکارهای باز</th>
            <td><?= pdm_num($baseStats['open_wos']) ?></td>
        </tr>
    </tbody>
</table>

</body>
</html>