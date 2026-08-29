<?php
/**
 * تحلیل حساسیت مسئله تخصیص
 * مسیر: app/software/or/views/sensitivity/_assignment_analysis.php
 */
?>

<!-- کارت‌های آماری -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="or-stat-card text-center">
            <h6 class="text-muted mb-2">مقدار بهینه</h6>
            <h3 class="mb-0 text-success fw-bold"><?= number_format($project['optimal_value'] ?? 0, 2) ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="or-stat-card text-center">
            <h6 class="text-muted mb-2">تعداد عوامل</h6>
            <h3 class="mb-0 text-primary fw-bold"><?= count($modelData['agents'] ?? []) ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="or-stat-card text-center">
            <h6 class="text-muted mb-2">تعداد وظایف</h6>
            <h3 class="mb-0 text-info fw-bold"><?= count($modelData['tasks'] ?? []) ?></h3>
        </div>
    </div>
</div>

<!-- تخصیص‌های بهینه -->
<?php if (!empty($analysis['assignment_sensitivity'])): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0"><i class="fas fa-check-double text-success"></i> تخصیص‌های بهینه و تحلیل حساسیت</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-light border mb-3">
            <small class="text-muted">
                <i class="fas fa-info-circle"></i>
                <strong>تفسیر:</strong> در مسئله تخصیص، هر عامل به یک وظیفه اختصاص می‌یابد. 
                allowable increase/decrease نشان می‌دهد که هزینه هر تخصیص تا چه حد می‌تواند تغییر کند 
                بدون اینکه ساختار بهینه تغییر کند.
            </small>
        </div>
        <div class="table-responsive">
            <table class="or-matrix">
                <thead>
                    <tr>
                        <th>عامل</th>
                        <th>وظیفه تخصیص‌یافته</th>
                        <th>هزینه فعلی</th>
                        <th>کاهش مجاز</th>
                        <th>افزایش مجاز</th>
                        <th>درصد از کل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalCost = $project['optimal_value'] ?? 1;
                    foreach ($analysis['assignment_sensitivity'] as $item): 
                        $percentage = $totalCost > 0 ? ($item['current_cost'] / $totalCost * 100) : 0;
                    ?>
                    <tr>
                        <td class="fw-bold">
                            <i class="fas fa-user-tie text-primary me-2"></i>
                            <?= $item['agent'] ?>
                        </td>
                        <td class="fw-bold">
                            <i class="fas fa-tasks text-info me-2"></i>
                            <?= $item['task'] ?>
                        </td>
                        <td class="text-success fw-bold"><?= number_format($item['current_cost'], 2) ?></td>
                        <td class="text-danger">
                            <?= is_numeric($item['allowable_decrease']) ? number_format($item['allowable_decrease'], 2) : $item['allowable_decrease'] ?>
                        </td>
                        <td class="text-success">
                            <?= is_numeric($item['allowable_increase']) ? number_format($item['allowable_increase'], 2) : $item['allowable_increase'] ?>
                        </td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-primary" role="progressbar" 
                                     style="width: <?= min(100, $percentage) ?>%">
                                    <?= number_format($percentage, 1) ?>%
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ماتریس هزینه کامل -->
<?php if (!empty($modelData['cost_matrix'])): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0"><i class="fas fa-table text-warning"></i> ماتریس هزینه کامل</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="or-matrix text-center">
                <thead>
                    <tr>
                        <th>عامل \ وظیفه</th>
                        <?php foreach ($modelData['tasks'] as $task): ?>
                            <th><?= $task['name'] ?? 'وظیفه' ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($modelData['agents'] as $i => $agent): ?>
                    <tr>
                        <th class="supply-demand-cell"><?= $agent['name'] ?? "عامل " . ($i+1) ?></th>
                        <?php foreach ($modelData['cost_matrix'][$i] ?? [] as $j => $cost): ?>
                            <td class="<?= isset($solution['assignments']) && 
                                         array_filter($solution['assignments'], fn($a) => $a['agent_index'] == $i && $a['task_index'] == $j) ? 'table-success fw-bold' : '' ?>">
                                <?= number_format($cost, 2) ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3 text-muted small">
            <span class="badge bg-success me-2">سلول‌های سبز</span> نشان‌دهنده تخصیص‌های بهینه هستند.
        </div>
    </div>
</div>
<?php endif; ?>