<?php
/**
 * تحلیل حساسیت مسئله کوتاه‌ترین مسیر
 * مسیر: app/software/or/views/sensitivity/_shortest_analysis.php
 */
?>

<!-- کارت‌های آماری -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="or-stat-card text-center">
            <h6 class="text-muted mb-2">فاصله بهینه</h6>
            <h3 class="mb-0 text-success fw-bold"><?= number_format($analysis['optimal_distance'] ?? 0, 2) ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="or-stat-card text-center">
            <h6 class="text-muted mb-2">تعداد گره‌ها</h6>
            <h3 class="mb-0 text-primary fw-bold"><?= count($modelData['nodes'] ?? []) ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="or-stat-card text-center">
            <h6 class="text-muted mb-2">تعداد یال‌ها</h6>
            <h3 class="mb-0 text-info fw-bold"><?= count($modelData['edges'] ?? []) ?></h3>
        </div>
    </div>
</div>

<!-- مسیر بهینه -->
<?php if (!empty($analysis['optimal_path'])): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-success text-white py-3">
        <h5 class="mb-0"><i class="fas fa-route"></i> مسیر بهینه</h5>
    </div>
    <div class="card-body text-center py-4">
        <div class="d-flex align-items-center justify-content-center flex-wrap gap-2">
            <?php foreach ($analysis['optimal_path'] as $idx => $node): ?>
                <div class="text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                         style="width: 50px; height: 50px; font-size: 1.2rem; font-weight: bold;">
                        <?= $node ?>
                    </div>
                    <small class="d-block mt-1 text-muted">گره <?= $idx + 1 ?></small>
                </div>
                <?php if ($idx < count($analysis['optimal_path']) - 1): ?>
                    <i class="fas fa-arrow-left text-success fa-2x mx-2"></i>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <div class="mt-4">
            <h4 class="text-success mb-0">
                <i class="fas fa-flag-checkered"></i>
                فاصله کل: <?= number_format($analysis['optimal_distance'] ?? 0, 2) ?>
            </h4>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- تحلیل حساسیت یال‌ها -->
<?php if (!empty($analysis['edge_sensitivity'])): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0"><i class="fas fa-exchange-alt text-primary"></i> تحلیل حساسیت یال‌ها</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-light border mb-3">
            <small class="text-muted">
                <i class="fas fa-info-circle"></i>
                <strong>تفسیر:</strong> یال‌های موجود در مسیر بهینه (با علامت ✓) حیاتی هستند. 
                تغییر وزن این یال‌ها مستقیماً بر فاصله بهینه تأثیر می‌گذارد.
                یال‌های خارج از مسیر می‌توانند تا allowable decrease تغییر کنند بدون اینکه مسیر بهینه تغییر کند.
            </small>
        </div>
        <div class="table-responsive">
            <table class="or-matrix">
                <thead>
                    <tr>
                        <th>از گره</th>
                        <th>به گره</th>
                        <th>وزن فعلی</th>
                        <th>در مسیر بهینه</th>
                        <th>کاهش مجاز</th>
                        <th>افزایش مجاز</th>
                        <th>تأثیر تغییر</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($analysis['edge_sensitivity'] as $edge): ?>
                    <tr class="<?= $edge['in_path'] ? 'table-success' : '' ?>">
                        <td class="fw-bold">
                            <i class="fas fa-circle text-primary me-1" style="font-size: 8px;"></i>
                            <?= $edge['from'] ?>
                        </td>
                        <td class="fw-bold">
                            <i class="fas fa-circle text-info me-1" style="font-size: 8px;"></i>
                            <?= $edge['to'] ?>
                        </td>
                        <td class="fw-bold"><?= number_format($edge['current_weight'], 2) ?></td>
                        <td>
                            <?php if ($edge['in_path']): ?>
                                <span class="badge bg-success"><i class="fas fa-check"></i> بله</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">خیر</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-danger">
                            <?= is_numeric($edge['allowable_decrease']) ? number_format($edge['allowable_decrease'], 2) : $edge['allowable_decrease'] ?>
                        </td>
                        <td class="text-success">
                            <?= is_numeric($edge['allowable_increase']) ? number_format($edge['allowable_increase'], 2) : $edge['allowable_increase'] ?>
                        </td>
                        <td>
                            <?php if ($edge['in_path']): ?>
                                <small class="text-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    تغییر مستقیم بر فاصله کل
                                </small>
                            <?php else: ?>
                                <small class="text-muted">بدون تأثیر تا آستانه</small>
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

<!-- خلاصه و توصیه‌ها -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-light py-3">
        <h5 class="mb-0"><i class="fas fa-lightbulb text-warning"></i> خلاصه و توصیه‌ها</h5>
    </div>
    <div class="card-body">
        <ul class="mb-0">
            <li class="mb-2">
                <strong>یال‌های حیاتی:</strong>
                <?= count(array_filter($analysis['edge_sensitivity'] ?? [], fn($e) => $e['in_path'])) ?>
                یال در مسیر بهینه وجود دارد که تغییر وزن آن‌ها مستقیماً فاصله کل را تغییر می‌دهد.
            </li>
            <li class="mb-2">
                <strong>یال‌های غیرحیاتی:</strong>
                <?= count(array_filter($analysis['edge_sensitivity'] ?? [], fn($e) => !$e['in_path'])) ?>
                یال خارج از مسیر بهینه هستند که تا allowable decrease می‌توانند کاهش یابند.
            </li>
            <li>
                <strong>پیشنهاد:</strong>
                برای کاهش فاصله کل، روی کاهش وزن یال‌های مسیر بهینه تمرکز کنید.
            </li>
        </ul>
    </div>
</div>