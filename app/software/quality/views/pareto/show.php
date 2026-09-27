<?php
$statusLabels = [
    'draft'        => ['label' => 'پیش‌نویس', 'class' => 'info'],
    'analyzed'     => ['label' => 'تحلیل‌شده', 'class' => 'warning'],
    'action_taken' => ['label' => 'اقدام انجام شد', 'class' => 'success'],
    'closed'       => ['label' => 'بسته‌شده', 'class' => 'inactive'],
];
$statusMeta = $statusLabels[$analysis['status']] ?? ['label' => $analysis['status'], 'class' => 'info'];
?>

<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <!-- Header -->
    <div class="qc-flex qc-flex-between qc-mb-4">
        <div>
            <div style="font-size:0.85rem; color:#94a3b8; margin-bottom:4px;">
                <a href="<?= CURRENT_MODULE_URL ?>?controller=pareto" style="color:#059669; text-decoration:none;">
                    <i class="fas fa-arrow-right"></i> بازگشت به لیست
                </a>
            </div>
            <h1 style="font-size:1.5rem; font-weight:700; color:#1f2937; margin:0;">
                <?= htmlspecialchars($analysis['title']) ?>
            </h1>
            <div style="margin-top:8px; display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                <span class="qc-status-badge qc-status-<?= $statusMeta['class'] ?>">
                    <?= $statusMeta['label'] ?>
                </span>
                <?php if ($analysis['analysis_date']): ?>
                    <span style="font-size:0.8rem; color:#64748b;">
                        <i class="fas fa-calendar"></i> <?= htmlspecialchars($analysis['analysis_date']) ?>
                    </span>
                <?php endif; ?>
                <?php if ($analysis['unit']): ?>
                    <span style="font-size:0.8rem; color:#64748b;">
                        <i class="fas fa-ruler"></i> واحد: <?= htmlspecialchars($analysis['unit']) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="qc-flex qc-gap-2">
            <a href="<?= CURRENT_MODULE_URL ?>?controller=pareto&action=edit&id=<?= $analysis['id'] ?>" class="qc-btn-outline">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <a href="<?= CURRENT_MODULE_URL ?>?controller=pareto&action=suggestCapa&id=<?= $analysis['id'] ?>" class="qc-btn-primary">
                <i class="fas fa-plus-circle"></i> تولید CAPA از Vital Few
            </a>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="qc-alert qc-alert-success qc-mb-3">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- شرح مسئله -->
    <?php if ($analysis['problem_statement']): ?>
        <div class="qc-card qc-mb-3">
            <div class="qc-card-body">
                <div style="font-size:0.8rem; color:#94a3b8; margin-bottom:6px;">
                    <i class="fas fa-info-circle"></i> شرح مسئله
                </div>
                <p style="color:#334155; line-height:1.7; margin:0;">
                    <?= nl2br(htmlspecialchars($analysis['problem_statement'])) ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Summary -->
    <div class="qc-stats-grid qc-mb-4">
        <div class="qc-stat-card">
            <div class="qc-stat-icon" style="background:#e0f2fe; color:#0369a1;">
                <i class="fas fa-sum"></i>
            </div>
            <div>
                <div class="qc-stat-value"><?= number_format((float)$analysis['total_value'], 0) ?></div>
                <div class="qc-stat-label">مجموع مقادیر</div>
            </div>
        </div>
        <div class="qc-stat-card danger">
            <div class="qc-stat-icon" style="background:#fee2e2; color:#dc2626;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <div class="qc-stat-value"><?= (int)$analysis['vital_few_count'] ?></div>
                <div class="qc-stat-label">Vital Few</div>
            </div>
        </div>
        <div class="qc-stat-card success">
            <div class="qc-stat-icon" style="background:#d1fae5; color:#047857;">
                <i class="fas fa-percentage"></i>
            </div>
            <div>
                <div class="qc-stat-value"><?= number_format((float)$analysis['vital_few_percent'], 1) ?>٪</div>
                <div class="qc-stat-label">سهم Vital Few</div>
            </div>
        </div>
        <div class="qc-stat-card info">
            <div class="qc-stat-icon" style="background:#e0e7ff; color:#4338ca;">
                <i class="fas fa-list"></i>
            </div>
            <div>
                <div class="qc-stat-value"><?= count($items) ?></div>
                <div class="qc-stat-label">کل دسته‌ها</div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title">
                <i class="fas fa-chart-bar"></i> نمودار Pareto
            </h3>
        </div>
        <div class="qc-card-body">
            <div style="position:relative; height:400px;">
                <canvas id="paretoChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="qc-card qc-mb-3">
        <div class="qc-card-header">
            <h3 class="qc-card-title">
                <i class="fas fa-table"></i> جزئیات دسته‌بندی‌ها
            </h3>
        </div>
        <div class="qc-card-body">
            <div class="qc-table-wrapper" style="overflow-x:auto;">
                <table class="qc-table">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>دسته</th>
                            <th>مقدار</th>
                            <th>درصد</th>
                            <th>تجمعی</th>
                            <th>دسته‌بندی</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $i => $item): ?>
                            <tr style="<?= $item['is_vital_few'] ? 'background:#fef2f2;' : '' ?>">
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($item['category_name']) ?></strong>
                                    <?php if ($item['notes']): ?>
                                        <div style="font-size:0.75rem; color:#94a3b8;">
                                            <?= htmlspecialchars($item['notes']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= number_format((float)$item['value'], 2) ?></td>
                                <td><?= number_format((float)$item['percent'], 2) ?>٪</td>
                                <td>
                                    <?= number_format((float)$item['cumulative_percent'], 2) ?>٪
                                    <div style="background:#e5e7eb; height:6px; border-radius:3px; margin-top:4px; overflow:hidden;">
                                        <div style="height:100%; width:<?= min(100, (float)$item['cumulative_percent']) ?>%;
                                                    background:<?= $item['is_vital_few'] ? '#dc2626' : '#9ca3af' ?>;"></div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($item['is_vital_few']): ?>
                                        <span class="qc-status-badge qc-status-danger">
                                            <i class="fas fa-fire"></i> Vital Few
                                        </span>
                                    <?php else: ?>
                                        <span class="qc-status-badge qc-status-inactive">
                                            Trivial Many
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- CAPA مرتبط -->
    <div class="qc-card">
        <div class="qc-card-header qc-flex qc-flex-between">
            <h3 class="qc-card-title">
                <i class="fas fa-tasks"></i> اقدامات اصلاحی مرتبط
                <span class="qc-chart-badge" style="margin-right:8px;"><?= count($capaActions) ?></span>
            </h3>
            <a href="<?= CURRENT_MODULE_URL ?>?controller=pareto&action=suggestCapa&id=<?= $analysis['id'] ?>"
               class="qc-btn-outline" style="padding:4px 10px; font-size:0.8rem;">
                <i class="fas fa-plus"></i> افزودن
            </a>
        </div>
        <div class="qc-card-body">
            <?php if (empty($capaActions)): ?>
                <div class="qc-empty-state" style="padding:1.5rem;">
                    <i class="fas fa-tasks" style="font-size:2rem; color:#cbd5e1;"></i>
                    <p style="color:#94a3b8; margin-top:0.5rem;">هنوز CAPA ای برای این تحلیل ثبت نشده</p>
                    <a href="<?= CURRENT_MODULE_URL ?>?controller=pareto&action=suggestCapa&id=<?= $analysis['id'] ?>"
                       class="qc-btn-primary" style="margin-top:0.5rem;">
                        <i class="fas fa-magic"></i> تولید خودکار از Vital Few
                    </a>
                </div>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <?php
                    $priorityLabels = [
                        'critical' => ['label' => 'بحرانی', 'color' => '#dc2626'],
                        'high'     => ['label' => 'بالا',   'color' => '#f59e0b'],
                        'medium'   => ['label' => 'متوسط',  'color' => '#3b82f6'],
                        'low'      => ['label' => 'پایین',  'color' => '#6b7280'],
                    ];
                    ?>
                    <?php foreach ($capaActions as $c): ?>
                        <?php $pmeta = $priorityLabels[$c['priority']] ?? ['label' => $c['priority'], 'color' => '#6b7280']; ?>
                        <div style="padding:12px; border:1px solid #e5e7eb; border-radius:8px; display:flex; justify-content:space-between; align-items:center;">
                            <div style="flex:1;">
                                <div style="display:flex; gap:8px; align-items:center;">
                                    <span style="background:<?= $pmeta['color'] ?>; color:#fff; padding:2px 8px; border-radius:4px; font-size:0.7rem;">
                                        <?= $pmeta['label'] ?>
                                    </span>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=capa&action=show&id=<?= $c['id'] ?>"
                                       style="color:#059669; font-weight:600; text-decoration:none;">
                                        <?= htmlspecialchars($c['title']) ?>
                                    </a>
                                </div>
                                <?php if ($c['due_date']): ?>
                                    <div style="font-size:0.75rem; color:#94a3b8; margin-top:4px;">
                                        <i class="fas fa-calendar"></i> سررسید: <?= htmlspecialchars($c['due_date']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <span class="qc-status-badge qc-status-<?= $c['status'] === 'closed' ? 'inactive' : 'warning' ?>">
                                    <?= htmlspecialchars($c['status']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="/public/assets/js/software/quality.js"></script>

<script>
window.PARETO_CHART_DATA = <?= json_encode($chart, JSON_UNESCAPED_UNICODE) ?>;
window.PARETO_UNIT = <?= json_encode($analysis['unit'] ?? '', JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="/public/assets/js/software/pareto.js?v=<?= time() ?>"></script>