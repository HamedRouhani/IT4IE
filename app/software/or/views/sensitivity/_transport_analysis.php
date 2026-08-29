<?php
/**
 * تحلیل حساسیت مسئله حمل و نقل و ترانشیپمنت
 * مسیر: app/software/or/views/sensitivity/_transport_analysis.php
 */
?>

<!-- کارت‌های آماری -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="or-stat-card text-center">
            <h6 class="text-muted mb-2">مقدار بهینه</h6>
            <h3 class="mb-0 text-success fw-bold"><?= number_format($project['optimal_value'] ?? 0, 2) ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="or-stat-card text-center">
            <h6 class="text-muted mb-2">تعداد مبادی</h6>
            <h3 class="mb-0 text-primary fw-bold"><?= count($analysis['sources'] ?? []) ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="or-stat-card text-center">
            <h6 class="text-muted mb-2">تعداد مقاصد</h6>
            <h3 class="mb-0 text-info fw-bold"><?= count($analysis['destinations'] ?? []) ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="or-stat-card text-center">
            <h6 class="text-muted mb-2">تعداد مسیرهای پایه</h6>
            <h3 class="mb-0 text-warning fw-bold">
                <?= count(array_filter($analysis['cost_sensitivity'] ?? [], fn($c) => $c['is_basic'])) ?>
            </h3>
        </div>
    </div>
</div>

<!-- قیمت‌های سایه‌ای عرضه و تقاضا -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0"><i class="fas fa-warehouse"></i> قیمت‌های سایه‌ای عرضه (ui)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>مبدأ</th>
                                <th>عرضه فعلی</th>
                                <th>قیمت سایه‌ای</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($analysis['sources'] as $i => $src): ?>
                            <tr>
                                <td class="fw-bold"><?= $src['name'] ?? "مبدأ " . ($i + 1) ?></td>
                                <td><?= number_format($src['capacity'] ?? 0) ?></td>
                                <td class="text-primary fw-bold">
                                    <?= number_format($analysis['shadow_prices_supply'][$i] ?? 0, 4) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-info text-white py-3">
                <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> قیمت‌های سایه‌ای تقاضا (vj)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>مقصد</th>
                                <th>تقاضای فعلی</th>
                                <th>قیمت سایه‌ای</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($analysis['destinations'] as $j => $dst): ?>
                            <tr>
                                <td class="fw-bold"><?= $dst['name'] ?? "مقصد " . ($j + 1) ?></td>
                                <td><?= number_format($dst['capacity'] ?? 0) ?></td>
                                <td class="text-info fw-bold">
                                    <?= number_format($analysis['shadow_prices_demand'][$j] ?? 0, 4) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- تحلیل حساسیت هزینه‌ها -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0"><i class="fas fa-dollar-sign text-success"></i> تحلیل حساسیت ضرایب هزینه (cij)</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-light border mb-3">
            <small class="text-muted">
                <i class="fas fa-info-circle"></i>
                <strong>تفسیر:</strong> سلول‌های پایه (Basic) نشان‌دهنده مسیرهای فعال در جواب بهینه هستند. 
                برای این سلول‌ها، allowable increase/decrease نامحدود است تا زمانی که ساختار پایه تغییر نکند.
            </small>
        </div>
        <div class="table-responsive">
            <table class="or-matrix text-center">
                <thead>
                    <tr>
                        <th>از مبدأ</th>
                        <th>به مقصد</th>
                        <th>هزینه فعلی</th>
                        <th>وضعیت</th>
                        <th>کاهش مجاز</th>
                        <th>افزایش مجاز</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($analysis['cost_sensitivity'] as $item): ?>
                    <tr class="<?= $item['is_basic'] ? 'table-success' : '' ?>">
                        <td class="fw-bold"><?= $item['from'] ?></td>
                        <td class="fw-bold"><?= $item['to'] ?></td>
                        <td><?= number_format($item['current_cost'], 2) ?></td>
                        <td>
                            <?php if ($item['is_basic']): ?>
                                <span class="badge bg-success">پایه (Basic)</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">غیرپایه</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-danger">
                            <?= is_numeric($item['allowable_decrease']) ? number_format($item['allowable_decrease'], 2) : $item['allowable_decrease'] ?>
                        </td>
                        <td class="text-success">
                            <?= is_numeric($item['allowable_increase']) ? number_format($item['allowable_increase'], 2) : $item['allowable_increase'] ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>