<?php
/**
 * گزارش کامل پروژه - قابل پرینت
 * مسیر: app/software/babok/views/reports/project_report.php
 */
$progressPercentage = $progress['completion_percentage'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Tahoma, Arial, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
            padding: 20px;
        }
        .report-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .report-header {
            border-bottom: 3px solid #6C3CE1;
            padding-bottom: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .report-title {
            font-size: 1.8rem;
            color: #6C3CE1;
            margin-bottom: 8px;
        }
        .report-meta {
            font-size: 0.85rem;
            color: #64748b;
        }
        .report-meta div { margin-bottom: 4px; }
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 1.2rem;
            color: #1e293b;
            border-right: 4px solid #6C3CE1;
            padding-right: 12px;
            margin-bottom: 15px;
            font-weight: 700;
        }
        .kpis-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .kpi-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .kpi-value { font-size: 2rem; font-weight: 800; margin-bottom: 5px; }
        .kpi-label { font-size: 0.85rem; opacity: 0.95; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        th {
            background: #f1f5f9;
            color: #1e293b;
            padding: 10px;
            text-align: right;
            border-bottom: 2px solid #cbd5e1;
            font-weight: 600;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        tr:hover { background: #f8fafc; }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-secondary { background: #f1f5f9; color: #475569; }
        .traceability-item {
            background: #f0f9ff;
            border-right: 4px solid #0ea5e9;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .technique-item {
            background: #fffbeb;
            border-right: 4px solid #f59e0b;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .progress-bar {
            width: 100%;
            background: #e2e8f0;
            border-radius: 99px;
            height: 10px;
            overflow: hidden;
            margin-top: 5px;
        }
        .progress-fill {
            background: linear-gradient(90deg, #10b981, #34d399);
            height: 100%;
            border-radius: 99px;
        }
        .print-actions {
            text-align: center;
            margin: 30px 0;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary { background: #6C3CE1; color: white; }
        .btn-secondary { background: #64748b; color: white; }
        .btn:hover { opacity: 0.9; }
        .footer-note {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 0.8rem;
            color: #94a3b8;
            text-align: center;
        }
        
        /* استایل‌های مخصوص پرینت */
        @media print {
            body { background: white; padding: 0; }
            .report-container { box-shadow: none; padding: 20px; }
            .print-actions { display: none !important; }
            .section { page-break-inside: avoid; }
            .kpi-card { background: #6C3CE1 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            th { background: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<div class="print-actions">
    <button onclick="window.print()" class="btn btn-primary">
        <i>🖨️</i> چاپ گزارش
    </button>
    <a href="?route=reports_export_tasks&id=<?= $project['id'] ?>" class="btn btn-secondary">
        <i>📊</i> خروجی Excel
    </a>
    <a href="?route=projects_view&id=<?= $project['id'] ?>" class="btn btn-secondary">
        <i>↩️</i> بازگشت به پروژه
    </a>
</div>

<div class="report-container">
    
    <!-- هدر گزارش -->
    <div class="report-header">
        <div>
            <div class="report-title">📋 گزارش جامع پروژه</div>
            <h2 style="color: #1e293b; margin-bottom: 10px;"><?= htmlspecialchars($project['name']) ?></h2>
            <div class="report-meta">
                <div><strong>فاز:</strong> <?= \App\Software\Babok\Helpers\Utils::phaseLabel($project['phase']) ?></div>
                <div><strong>متدولوژی:</strong> <?= \App\Software\Babok\Helpers\Utils::methodologyLabel($project['methodology']) ?></div>
                <div><strong>تعداد ذی‌نفعان:</strong> <?= $project['stakeholder_count'] ?></div>
            </div>
        </div>
        <div style="text-align: left; font-size: 0.8rem; color: #64748b;">
            <div><strong>تاریخ تولید:</strong></div>
            <div><?= date('Y/m/d - H:i', strtotime($generatedAt)) ?></div>
            <div style="margin-top: 8px;"><strong>تهیه‌کننده:</strong></div>
            <div><?= htmlspecialchars($generatedBy) ?></div>
        </div>
    </div>

    <?php if (!empty($project['description'])): ?>
    <div class="section">
        <h3 class="section-title">📝 خلاصه اجرایی</h3>
        <p style="color: #475569; line-height: 1.8;"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
    </div>
    <?php endif; ?>

    <!-- شاخص‌های کلیدی عملکرد -->
    <?php if (!empty($analytics['kpis'])): ?>
    <div class="section">
        <h3 class="section-title">📊 شاخص‌های کلیدی عملکرد (KPIs)</h3>
        <div class="kpis-grid">
            <div class="kpi-card">
                <div class="kpi-value"><?= $analytics['kpis']['health_index'] ?></div>
                <div class="kpi-label">شاخص سلامت</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value"><?= $analytics['kpis']['completion_rate'] ?>%</div>
                <div class="kpi-label">نرخ تکمیل</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value"><?= $analytics['kpis']['avg_quality'] ?></div>
                <div class="kpi-label">میانگین کیفیت</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value"><?= $analytics['kpis']['ka_coverage'] ?>%</div>
                <div class="kpi-label">پوشش حوزه‌ها</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- پیشرفت بر اساس حوزه دانشی -->
    <?php if (!empty($analytics['knowledge_area_progress'])): ?>
    <div class="section">
        <h3 class="section-title">🎯 پیشرفت بر اساس حوزه‌های دانشی</h3>
        <table>
            <thead>
                <tr>
                    <th>کد</th>
                    <th>حوزه دانشی</th>
                    <th>پیشرفت</th>
                    <th>کیفیت</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($analytics['knowledge_area_progress'] as $ka): ?>
                    <?php $kaProgress = $ka['total_tasks'] > 0 ? round(($ka['completed_tasks'] / $ka['total_tasks']) * 100) : 0; ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($ka['code']) ?></strong></td>
                        <td><?= htmlspecialchars($ka['name']) ?></td>
                        <td>
                            <div><?= $kaProgress ?>% (<?= $ka['completed_tasks'] ?>/<?= $ka['total_tasks'] ?>)</div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $kaProgress ?>%;"></div>
                            </div>
                        </td>
                        <td>
                            <?php if ($ka['avg_quality'] > 0): ?>
                                <span class="badge <?= $ka['avg_quality'] >= 80 ? 'badge-success' : ($ka['avg_quality'] >= 60 ? 'badge-warning' : 'badge-danger') ?>">
                                    <?= $ka['avg_quality'] ?>/100
                                </span>
                            <?php else: ?>
                                <span class="badge badge-secondary">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- کیفیت نیازمندی‌ها -->
    <?php if (!empty($qualityStats) && $qualityStats['total_tasks'] > 0): ?>
    <div class="section">
        <h3 class="section-title">✨ تحلیل کیفیت نیازمندی‌ها</h3>
        <table>
            <tr>
                <td><strong>میانگین کیفیت:</strong></td>
                <td><span class="badge badge-info"><?= number_format($qualityStats['avg_score'] ?? 0, 1) ?>/100</span></td>
            </tr>
            <tr>
                <td><strong>نیازمندی‌های عالی (≥80):</strong></td>
                <td><span class="badge badge-success"><?= $qualityStats['excellent_count'] ?? 0 ?></span></td>
            </tr>
            <tr>
                <td><strong>نیازمندی‌های قابل قبول (60-79):</strong></td>
                <td><span class="badge badge-warning"><?= $qualityStats['good_count'] ?? 0 ?></span></td>
            </tr>
            <tr>
                <td><strong>نیاز به بازنگری (<60):</strong></td>
                <td><span class="badge badge-danger"><?= $qualityStats['needs_improvement_count'] ?? 0 ?></span></td>
            </tr>
        </table>
    </div>
    <?php endif; ?>

    <!-- لیست وظایف -->
    <?php if (!empty($tasks)): ?>
    <div class="section">
        <h3 class="section-title">📋 فهرست وظایف پروژه (<?= count($tasks) ?>)</h3>
        <table>
            <thead>
                <tr>
                    <th>کد</th>
                    <th>نام وظیفه</th>
                    <th>حوزه دانشی</th>
                    <th>وضعیت</th>
                    <th>کیفیت</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <?php $score = $task['quality_score'] ?? 0; ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($task['task_code']) ?></strong></td>
                        <td><?= htmlspecialchars($task['task_name']) ?></td>
                        <td><?= htmlspecialchars($task['knowledge_area_name']) ?></td>
                        <td>
                            <span class="badge badge-<?= $task['status'] === 'completed' ? 'success' : ($task['status'] === 'in_progress' ? 'warning' : 'secondary') ?>">
                                <?= \App\Software\Babok\Helpers\Utils::statusLabel($task['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($score > 0): ?>
                                <span class="badge <?= $score >= 80 ? 'badge-success' : ($score >= 60 ? 'badge-warning' : 'badge-danger') ?>">
                                    <?= $score ?>/100
                                </span>
                            <?php else: ?>
                                <span class="badge badge-secondary">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- تکنیک‌های پیشنهادی -->
    <?php if (!empty($techniques)): ?>
    <div class="section">
        <h3 class="section-title">💡 تکنیک‌های پیشنهادی هوشمند</h3>
        <?php foreach ($techniques as $index => $tech): ?>
            <?php $t = $tech['technique'] ?? []; ?>
            <div class="technique-item">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <strong style="color: #92400e;">#<?= $index + 1 ?> - <?= htmlspecialchars($t['name'] ?? 'نامشخص') ?></strong>
                    <span class="badge badge-warning">امتیاز: <?= $tech['score'] ?? 0 ?></span>
                </div>
                <div style="font-size: 0.85rem; color: #78350f;">
                    <?= htmlspecialchars(mb_substr($t['description'] ?? '', 0, 200)) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- پیشنهادات ردیابی -->
    <?php if (!empty($traceability)): ?>
    <div class="section">
        <h3 class="section-title">🔗 پیشنهادات ردیابی نیازمندی‌ها</h3>
        <?php foreach ($traceability as $s): ?>
            <div class="traceability-item">
                <div style="font-weight: 600; color: #0369a1; margin-bottom: 5px;">
                    <?= htmlspecialchars($s['source_task_name']) ?> ➔ <?= htmlspecialchars($s['target_task_name']) ?>
                </div>
                <div style="font-size: 0.85rem; color: #475569;">
                    <?= htmlspecialchars($s['recommendation']) ?>
                </div>
                <?php if (!empty($s['shared_artifacts'])): ?>
                    <div style="margin-top: 5px; font-size: 0.8rem; color: #0369a1;">
                        <strong>مستندات مشترک:</strong> <?= htmlspecialchars($s['shared_artifacts']) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- پاورقی -->
    <div class="footer-note">
        این گزارش به صورت خودکار توسط سیستم BABOK Analyzer تولید شده است.<br>
        تاریخ تولید: <?= date('Y/m/d H:i:s') ?> | تهیه‌کننده: <?= htmlspecialchars($generatedBy) ?>
    </div>

</div>

<div class="print-actions">
    <button onclick="window.print()" class="btn btn-primary">
        <i>🖨️</i> چاپ گزارش
    </button>
    <a href="?route=projects_view&id=<?= $project['id'] ?>" class="btn btn-secondary">
        <i>↩️</i> بازگشت به پروژه
    </a>
</div>

</body>
</html>