<?php
/**
 * تحلیل حساسیت برنامه‌ریزی خطی
 * مسیر: app/software/or/views/sensitivity/_lp_analysis.php
 */
?>

<!-- کارت‌های آماری -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="or-stat-card text-center">
            <h6 class="text-muted mb-2">مقدار بهینه (Z)</h6>
            <h3 class="mb-0 text-success fw-bold"><?= number_format($project['optimal_value'] ?? 0, 4) ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="or-stat-card text-center">
            <h6 class="text-muted mb-2">تعداد متغیرها</h6>
            <h3 class="mb-0 text-primary fw-bold"><?= count($modelData['c'] ?? []) ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="or-stat-card text-center">
            <h6 class="text-muted mb-2">تعداد محدودیت‌ها</h6>
            <h3 class="mb-0 text-info fw-bold"><?= count($modelData['b'] ?? []) ?></h3>
        </div>
    </div>
</div>

<!-- مقادیر بهینه متغیرها -->
<?php if (!empty($solution['solution'])): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0"><i class="fas fa-star text-warning"></i> مقادیر بهینه متغیرها</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ($solution['solution'] as $i => $value): ?>
                <div class="col-md-3">
                    <div class="border rounded p-3 text-center">
                        <h6 class="text-muted mb-2">x<sub><?= $i + 1 ?></sub></h6>
                        <h4 class="mb-0 text-primary"><?= number_format($value, 4) ?></h4>
                        <?php if ($value > 0): ?>
                            <small class="text-success">پایه (Basic)</small>
                        <?php else: ?>
                            <small class="text-muted">غیرپایه</small>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- قیمت‌های سایه‌ای -->
<?php if (!empty($analysis['shadow_prices'])): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0"><i class="fas fa-dollar-sign text-success"></i> قیمت‌های سایه‌ای (Shadow Prices)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="or-matrix">
                <thead>
                    <tr>
                        <th>محدودیت</th>
                        <th>قیمت سایه‌ای</th>
                        <th>تفسیر</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($analysis['shadow_prices'] as $i => $price): ?>
                    <tr>
                        <td class="fw-bold">محدودیت <?= $i + 1 ?></td>
                        <td class="text-success fw-bold"><?= number_format($price, 4) ?></td>
                        <td>
                            <?php if (abs($price) > 0.0001): ?>
                                <small class="text-muted">
                                    افزایش یک واحد در RHS، تابع هدف را 
                                    <span class="text-success"><?= number_format(abs($price), 4) ?></span> 
                                    واحد <?= $price > 0 ? 'بهبود' : 'تضعیف' ?> می‌دهد.
                                </small>
                            <?php else: ?>
                                <small class="text-muted">محدودیت غیرفعال (Slack دارد)</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- محدوده تغییرات ضرایب تابع هدف -->
<?php if (!empty($analysis['objective_ranges'])): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0"><i class="fas fa-sliders-h text-primary"></i> محدوده مجاز تغییرات ضرایب تابع هدف</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="or-matrix text-center">
                <thead>
                    <tr>
                        <th>متغیر</th>
                        <th>ضریب فعلی</th>
                        <th>کاهش مجاز</th>
                        <th>افزایش مجاز</th>
                        <th>بازه مجاز</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($analysis['objective_ranges'] as $range): 
                        $current = $range['current_coeff'] ?? 0;
                        $dec = $range['allowable_decrease'] ?? 'نامحدود';
                        $inc = $range['allowable_increase'] ?? 'نامحدود';
                        $minVal = is_numeric($dec) ? ($current - $dec) : '-∞';
                        $maxVal = is_numeric($inc) ? ($current + $inc) : '+∞';
                    ?>
                    <tr>
                        <td class="fw-bold"><?= $range['variable'] ?? 'x' ?></td>
                        <td><?= number_format($current, 4) ?></td>
                        <td class="text-danger"><?= is_numeric($dec) ? number_format($dec, 4) : 'نامحدود' ?></td>
                        <td class="text-success"><?= is_numeric($inc) ? number_format($inc, 4) : 'نامحدود' ?></td>
                        <td class="fw-bold text-primary">
                            [ <?= is_numeric($minVal) ? number_format($minVal, 4) : '-∞' ?> , <?= is_numeric($maxVal) ? number_format($maxVal, 4) : '+∞' ?> ]
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- محدوده تغییرات RHS -->
<?php if (!empty($analysis['rhs_ranges'])): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0"><i class="fas fa-balance-scale text-info"></i> محدوده مجاز تغییرات سمت راست (RHS)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="or-matrix text-center">
                <thead>
                    <tr>
                        <th>محدودیت</th>
                        <th>RHS فعلی</th>
                        <th>قیمت سایه‌ای</th>
                        <th>کاهش مجاز</th>
                        <th>افزایش مجاز</th>
                        <th>بازه مجاز</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($analysis['rhs_ranges'] as $range): 
                        $currentRhs = $range['current_rhs'] ?? 0;
                        $shadow = $range['shadow_price'] ?? 0;
                        $dec = $range['allowable_decrease'] ?? 'نامحدود';
                        $inc = $range['allowable_increase'] ?? 'نامحدود';
                        $minRhs = is_numeric($dec) ? ($currentRhs - $dec) : '-∞';
                        $maxRhs = is_numeric($inc) ? ($currentRhs + $inc) : '+∞';
                    ?>
                    <tr>
                        <td class="fw-bold"><?= $range['constraint'] ?? 'محدودیت' ?></td>
                        <td><?= number_format($currentRhs, 4) ?></td>
                        <td class="fw-bold <?= $shadow > 0 ? 'text-success' : 'text-muted' ?>"><?= number_format($shadow, 4) ?></td>
                        <td class="text-danger"><?= is_numeric($dec) ? number_format($dec, 4) : 'نامحدود' ?></td>
                        <td class="text-success"><?= is_numeric($inc) ? number_format($inc, 4) : 'نامحدود' ?></td>
                        <td class="fw-bold text-primary">
                            [ <?= is_numeric($minRhs) ? number_format($minRhs, 4) : '-∞' ?> , <?= is_numeric($maxRhs) ? number_format($maxRhs, 4) : '+∞' ?> ]
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>