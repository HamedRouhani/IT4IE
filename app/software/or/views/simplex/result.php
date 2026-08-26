<?php
/**
 * نمایش نتایج حل مدل برنامه‌ریزی خطی
 * مسیر: app/software/or/views/simplex/result.php
 */

$solution = json_decode($project['solution_data'] ?? '{}', true);
$modelData = json_decode($project['model_data'] ?? '{}', true);
?>

<div class="container-fluid py-4">
    <!-- هدر صفحه -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-check-circle text-success"></i> نتایج حل مدل
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('') ?>">OR Analyzer</a></li>
                    <li class="breadcrumb-item"><a href="<?= or_url('controller=simplex') ?>">برنامه‌ریزی خطی</a></li>
                    <li class="breadcrumb-item active"><?= or_e($project['name']) ?></li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="<?= or_url('controller=simplex&action=create') ?>" class="btn btn-or-primary">
                <i class="fas fa-plus"></i> مدل جدید
            </a>
        </div>
    </div>

    <!-- کارت‌های آماری -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="or-stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon indigo">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">مقدار بهینه</h6>
                        <h3 class="mb-0 text-success">
                            <?= number_format($solution['optimal_value'] ?? 0, 2) ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="or-stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon cyan">
                        <i class="fas fa-infinity"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">تعداد تکرار</h6>
                        <h3 class="mb-0"><?= $solution['iterations'] ?? 0 ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="or-stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon green">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">وضعیت</h6>
                        <h5 class="mb-0 text-success"><?= $solution['status'] ?? 'نامشخص' ?></h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="or-stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon amber">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">تعداد متغیرها</h6>
                        <h3 class="mb-0"><?= count($modelData['c'] ?? []) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- جواب بهینه -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0">
                <i class="fas fa-star text-warning"></i> جواب بهینه
            </h5>
        </div>
        <div class="card-body">
            <?php if (!empty($solution['solution'])): ?>
                <div class="row g-3">
                    <?php foreach ($solution['solution'] as $i => $value): ?>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <h6 class="text-muted mb-2">x<sub><?= $i + 1 ?></sub></h6>
                                <h4 class="mb-0 text-primary"><?= number_format($value, 4) ?></h4>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle"></i> جواب بهینه‌ای یافت نشد.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- تحلیل حساسیت -->
    <?php if (!empty($solution['shadow_prices']) || !empty($solution['sensitivity_report'])): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0">
                <i class="fas fa-chart-area text-info"></i> تحلیل حساسیت
            </h5>
        </div>
        <div class="card-body">
            <!-- قیمت‌های سایه‌ای -->
            <?php if (!empty($solution['shadow_prices'])): ?>
            <h6 class="mb-3">
                <i class="fas fa-dollar-sign text-success"></i> قیمت‌های سایه‌ای (Shadow Prices)
            </h6>
            <div class="table-responsive mb-4">
                <table class="or-matrix">
                    <thead>
                        <tr>
                            <th>محدودیت</th>
                            <th>قیمت سایه‌ای</th>
                            <th>تفسیر</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solution['shadow_prices'] as $i => $price): ?>
                        <tr>
                            <td class="fw-bold">محدودیت <?= $i + 1 ?></td>
                            <td class="text-success fw-bold"><?= number_format($price, 4) ?></td>
                            <td>
                                <?php if ($price > 0): ?>
                                    <small class="text-muted">
                                        افزایش واحد در RHS، تابع هدف را 
                                        <span class="text-success"><?= number_format($price, 4) ?></span> 
                                        واحد بهبود می‌دهد.
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
            <?php endif; ?>

            <!-- محدوده تغییرات -->
            <?php if (!empty($solution['sensitivity_report'])): ?>
            <h6 class="mb-3">
                <i class="fas fa-sliders-h text-primary"></i> محدوده مجاز تغییرات ضرایب
            </h6>
            <div class="table-responsive">
                <table class="or-matrix">
                    <thead>
                        <tr>
                            <th>متغیر</th>
                            <th>ضریب فعلی</th>
                            <th>حداقل مجاز</th>
                            <th>حداکثر مجاز</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solution['sensitivity_report'] as $i => $range): ?>
                        <tr>
                            <td class="fw-bold">x<sub><?= $i + 1 ?></sub></td>
                            <td><?= number_format($modelData['c'][$i] ?? 0, 4) ?></td>
                            <td><?= $range['allowable_decrease'] ?? 'نامحدود' ?></td>
                            <td><?= $range['allowable_increase'] ?? 'نامحدود' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- دکمه‌های عملیات -->
    <div class="d-flex justify-content-between">
        <a href="<?= or_url('controller=simplex') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-list"></i> بازگشت به لیست
        </a>
        <div>
            <button type="button" class="btn btn-or-warning" onclick="exportResults()">
                <i class="fas fa-download"></i> خروجی Excel
            </button>
        </div>
    </div>
</div>

<script>
// خروجی گرفتن نتایج
function exportResults() {
    const solution = <?= json_encode($solution) ?>;
    const modelData = <?= json_encode($modelData) ?>;
    
    let csv = 'نتایج حل مدل برنامه‌ریزی خطی\n\n';
    csv += `نام مدل,${modelData.name}\n`;
    csv += `مقدار بهینه,${solution.optimal_value}\n`;
    csv += `تعداد تکرار,${solution.iterations}\n\n`;
    
    csv += 'جواب بهینه\n';
    csv += 'متغیر,مقدار\n';
    solution.solution.forEach((val, i) => {
        csv += `x${i+1},${val}\n`;
    });
    
    if (solution.shadow_prices) {
        csv += '\nقیمت‌های سایه‌ای\n';
        csv += 'محدودیت,قیمت\n';
        solution.shadow_prices.forEach((price, i) => {
            csv += `محدودیت ${i+1},${price}\n`;
        });
    }
    
    const blob = new Blob(['\uFEFF' + csv], {type: 'text/csv;charset=utf-8;'});
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `simplex_result_${Date.now()}.csv`;
    link.click();
}
</script>