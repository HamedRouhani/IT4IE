<?php
/**
 * OR Analyzer - گزارش تفصیلی یک پروژه (نسخه نهایی)
 * مسیر: app/software/or/views/report/show.php
 */

// Parse کردن داده‌های JSON
$modelData = json_decode($project['model_data'] ?? '{}', true) ?: [];
$solutionData = json_decode($project['solution_data'] ?? '{}', true) ?: [];
$problemTypeCode = $project['problem_type_code'] ?? 'UNKNOWN';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= or_url('') ?>"><i class="fas fa-home"></i> OR Analyzer</a></li>
            <li class="breadcrumb-item"><a href="<?= or_url('controller=report') ?>">گزارش‌ها</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($project['name']) ?></li>
        </ol>
    </nav>

    <!-- اطلاعات کلی پروژه -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-file-alt text-primary me-2"></i> گزارش تفصیلی پروژه</h5>
            <div>
                <button class="btn btn-sm btn-outline-info me-2" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> چاپ
                </button>
                <a href="<?= or_url('controller=report') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-right me-1"></i> بازگشت
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <h6 class="text-muted small text-uppercase fw-bold">نام پروژه</h6>
                    <p class="fw-bold fs-5 mb-0"><?= htmlspecialchars($project['name']) ?></p>
                </div>
                <div class="col-md-3">
                    <h6 class="text-muted small text-uppercase fw-bold">نوع مسئله</h6>
                    <p class="fw-bold fs-5 mb-0"><?= htmlspecialchars($project['problem_type_name'] ?? 'نامشخص') ?></p>
                </div>
                <div class="col-md-3">
                    <h6 class="text-muted small text-uppercase fw-bold">وضعیت</h6>
                    <span class="badge bg-<?= $project['status'] === 'solved' ? 'success' : 'warning' ?> fs-6">
                        <?= $project['status'] === 'solved' ? '✓ حل شده' : ucfirst($project['status']) ?>
                    </span>
                </div>
                <div class="col-md-3">
                    <h6 class="text-muted small text-uppercase fw-bold">تاریخ حل</h6>
                    <p class="fw-bold fs-5 mb-0"><?= htmlspecialchars($project['updated_at'] ?? 'ثبت نشده') ?></p>
                </div>
            </div>

            <?php if (!empty($project['description'])): ?>
                <hr class="my-3">
                <div class="mb-3">
                    <h6 class="text-muted small text-uppercase fw-bold">توضیحات</h6>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- بررسی وجود جواب -->
    <?php if ($project['status'] !== 'solved' || empty($solutionData)): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            این پروژه هنوز حل نشده است یا داده‌ای برای نمایش وجود ندارد.
        </div>
    <?php else: ?>

        <!-- نمایش بر اساس نوع مسئله -->
        <?php if ($problemTypeCode === 'LP'): ?>
            <!-- ============ برنامه‌ریزی خطی (LP) ============ -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i> نتایج بهینه‌سازی خطی (Simplex)</h5>
                </div>
                <div class="card-body">
                    <!-- خلاصه نتایج -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block">مقدار بهینه (Z)</small>
                                <h3 class="mb-0 text-success fw-bold">
                                    <?= number_format($solutionData['optimal_value'] ?? 0, 4) ?>
                                </h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block">تعداد تکرار</small>
                                <h3 class="mb-0 text-primary fw-bold"><?= $solutionData['iterations'] ?? 0 ?></h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block">وضعیت</small>
                                <h4 class="mb-0 text-success fw-bold">
                                    <?= $solutionData['status'] === 'optimal' ? '✓ بهینه' : ucfirst($solutionData['status'] ?? 'نامشخص') ?>
                                </h4>
                            </div>
                        </div>
                    </div>

                    <!-- مقادیر متغیرهای تصمیم -->
                    <?php if (!empty($solutionData['solution'])): ?>
                        <h6 class="fw-bold mb-3"><i class="fas fa-variable me-2"></i> مقادیر بهینه متغیرهای تصمیم</h6>
                        <div class="row g-3 mb-4">
                            <?php foreach ($solutionData['solution'] as $i => $value): ?>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 text-center">
                                        <h6 class="text-muted mb-2">x<sub><?= $i + 1 ?></sub></h6>
                                        <h4 class="mb-0 text-primary"><?= number_format($value, 4) ?></h4>
                                        <?php if ($value > 0.0001): ?>
                                            <small class="text-success">پایه (Basic)</small>
                                        <?php else: ?>
                                            <small class="text-muted">غیرپایه (Zero)</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- قیمت‌های سایه‌ای (Shadow Prices) -->
                    <?php if (!empty($solutionData['shadow_prices'])): ?>
                        <h6 class="fw-bold mb-3"><i class="fas fa-dollar-sign me-2"></i> قیمت‌های سایه‌ای (Shadow Prices)</h6>
                        <p class="text-muted small">این مقادیر نشان می‌دهند که با افزایش یک واحد در سمت راست محدودیت، تابع هدف چقدر بهبود می‌یابد.</p>
                        <div class="table-responsive mb-4">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>محدودیت</th>
                                        <th>قیمت سایه‌ای</th>
                                        <th>تفسیر</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($solutionData['shadow_prices'] as $i => $price): ?>
                                        <tr>
                                            <td class="fw-bold">محدودیت <?= $i + 1 ?></td>
                                            <td class="text-success fw-bold"><?= number_format($price, 4) ?></td>
                                            <td>
                                                <?php if (abs($price) > 0.0001): ?>
                                                    <small class="text-muted">
                                                        محدودیت فعال: هر واحد افزایش در RHS، 
                                                        تابع هدف را <span class="text-success"><?= number_format(abs($price), 4) ?></span> 
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
                </div>
            </div>

        <?php elseif ($problemTypeCode === 'TRANS'): ?>
            <!-- ============ حمل و نقل (Transportation) ============ -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-truck me-2"></i> نتایج مسئله حمل و نقل</h5>
                </div>
                <div class="card-body">
                    <!-- خلاصه نتایج -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block">هزینه کل بهینه</small>
                                <h3 class="mb-0 text-success fw-bold">
                                    <?= number_format($solutionData['optimal_cost'] ?? 0, 2) ?>
                                </h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block">هزینه اولیه</small>
                                <h4 class="mb-0 text-primary"><?= number_format($solutionData['initial_cost'] ?? 0, 2) ?></h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block">روش حل</small>
                                <h5 class="mb-0 text-info"><?= htmlspecialchars($solutionData['method'] ?? 'نامشخص') ?></h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block">تعداد تکرار</small>
                                <h4 class="mb-0 text-warning"><?= $solutionData['iterations'] ?? 0 ?></h4>
                            </div>
                        </div>
                    </div>

                    <!-- وضعیت خاص -->
                    <?php if (!empty($solutionData['is_degenerate']) && $solutionData['is_degenerate']): ?>
                        <div class="alert alert-warning mb-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>مسئله دژنره است:</strong> تعداد سلول‌های پایه کمتر از m+n-1 است.
                        </div>
                    <?php endif; ?>

                    <!-- ماتریس تخصیص -->
                    <?php if (!empty($solutionData['allocation'])): ?>
                        <h6 class="fw-bold mb-3"><i class="fas fa-table me-2"></i> ماتریس تخصیص بهینه</h6>
                        <p class="text-muted small">اعداد داخل سلول‌ها مقدار حمل از مبدأ به مقصد را نشان می‌دهند. سلول‌های <span class="badge bg-primary">هایلایت شده</span> متغیرهای پایه هستند.</p>
                        
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>مبدأ \ مقصد</th>
                                        <?php 
                                        $n = count($solutionData['allocation'][0] ?? []);
                                        for ($j = 0; $j < $n; $j++): 
                                        ?>
                                            <th>مقصد <?= $j + 1 ?></th>
                                        <?php endfor; ?>
                                        <th class="bg-warning">عرضه</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($solutionData['allocation'] as $i => $row): ?>
                                        <tr>
                                            <th class="bg-light">مبدأ <?= $i + 1 ?></th>
                                            <?php foreach ($row as $j => $alloc): 
                                                $isBasic = false;
                                                if (!empty($solutionData['basic_cells'])) {
                                                    foreach ($solutionData['basic_cells'] as $bc) {
                                                        if ($bc[0] == $i && $bc[1] == $j) {
                                                            $isBasic = true;
                                                            break;
                                                        }
                                                    }
                                                }
                                            ?>
                                                <td class="<?= $isBasic ? 'table-primary fw-bold' : '' ?>">
                                                    <?= $alloc > 0 ? number_format($alloc, 0) : '-' ?>
                                                </td>
                                            <?php endforeach; ?>
                                            <td class="bg-warning fw-bold">
                                                <?= $modelData['sources'][$i]['capacity'] ?? '-' ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="bg-warning">
                                        <th class="fw-bold">تقاضا</th>
                                        <?php for ($j = 0; $j < $n; $j++): ?>
                                            <td class="fw-bold">
                                                <?= $modelData['destinations'][$j]['demand'] ?? '-' ?>
                                            </td>
                                        <?php endfor; ?>
                                        <td class="bg-success text-white fw-bold">
                                            <?= $solutionData['total_supply'] ?? '-' ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!-- بازخورد هوشمند -->
                    <?php if (!empty($solutionData['smart_feedback'])): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>تحلیل:</strong> <?= htmlspecialchars($solutionData['smart_feedback']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($problemTypeCode === 'ASSIGN'): ?>
            <!-- ============ تخصیص (Assignment) ============ -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i> نتایج مسئله تخصیص</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block">هزینه/زمان کل بهینه</small>
                                <h3 class="mb-0 text-success fw-bold">
                                    <?= number_format($solutionData['optimal_cost'] ?? 0, 2) ?>
                                </h3>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block">تعداد تخصیص‌های موفق</small>
                                <h3 class="mb-0 text-primary fw-bold">
                                    <?= count($solutionData['basic_cells'] ?? []) ?>
                                </h3>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($solutionData['allocation'])): ?>
                        <h6 class="fw-bold mb-3"><i class="fas fa-check-circle me-2"></i> جدول تخصیص بهینه</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>عامل \ وظیفه</th>
                                        <?php 
                                        $n = count($solutionData['allocation'][0] ?? []);
                                        for ($j = 0; $j < $n; $j++): 
                                        ?>
                                            <th>وظیفه <?= $j + 1 ?></th>
                                        <?php endfor; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($solutionData['allocation'] as $i => $row): ?>
                                        <tr>
                                            <th class="bg-light">عامل <?= $i + 1 ?></th>
                                            <?php foreach ($row as $j => $alloc): 
                                                $isBasic = false;
                                                if (!empty($solutionData['basic_cells'])) {
                                                    foreach ($solutionData['basic_cells'] as $bc) {
                                                        if ($bc[0] == $i && $bc[1] == $j) {
                                                            $isBasic = true;
                                                            break;
                                                        }
                                                    }
                                                }
                                            ?>
                                                <td class="<?= $isBasic ? 'table-success fw-bold' : '' ?>">
                                                    <?= $alloc > 0 ? '✓' : '-' ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($problemTypeCode === 'SHORTEST'): ?>
            <!-- ============ کوتاه‌ترین مسیر (Shortest Path) ============ -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark py-3">
                    <h5 class="mb-0"><i class="fas fa-route me-2"></i> نتایج کوتاه‌ترین مسیر</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block">فاصله/هزینه کل مسیر</small>
                                <h3 class="mb-0 text-success fw-bold">
                                    <?= number_format($solutionData['optimal_cost'] ?? $solutionData['distance'] ?? 0, 2) ?>
                                </h3>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block">الگوریتم استفاده شده</small>
                                <h5 class="mb-0 text-info"><?= htmlspecialchars($solutionData['method'] ?? 'Dijkstra') ?></h5>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($solutionData['path'])): ?>
                        <h6 class="fw-bold mb-3"><i class="fas fa-map-signs me-2"></i> مسیر بهینه</h6>
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <?php foreach ($solutionData['path'] as $idx => $node): ?>
                                <div class="px-3 py-2 bg-primary text-white rounded">
                                    <?= htmlspecialchars(is_array($node) ? ($node['name'] ?? "گره {$node}") : $node) ?>
                                </div>
                                <?php if ($idx < count($solutionData['path']) - 1): ?>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-arrow-left text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <!-- ============ سایر انواع مسئله ============ -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-secondary text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> نتایج مسئله</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block">مقدار بهینه</small>
                                <h3 class="mb-0 text-success fw-bold">
                                    <?= number_format($solutionData['optimal_value'] ?? $solutionData['optimal_cost'] ?? 0, 4) ?>
                                </h3>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block">وضعیت</small>
                                <h4 class="mb-0 text-success"><?= ucfirst($solutionData['status'] ?? 'نامشخص') ?></h4>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($solutionData['message'])): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <?= htmlspecialchars($solutionData['message']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <!-- بخش داده‌های خام (با دکمه toggle) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-white py-3">
            <h6 class="mb-0">
                <i class="fas fa-code me-2"></i> داده‌های خام (JSON) 
                <small class="text-muted ms-2">[فقط برای توسعه‌دهندگان]</small>
            </h6>
        </div>
        <div class="card-body">
            <div class="d-flex gap-2 mb-3">
                <button class="btn btn-sm btn-outline-secondary" onclick="toggleJson('solution')" id="toggleSolutionBtn">
                    <i class="fas fa-eye me-1"></i> <span>نمایش</span> solution_data
                </button>
                <button class="btn btn-sm btn-outline-secondary" onclick="toggleJson('model')" id="toggleModelBtn">
                    <i class="fas fa-eye me-1"></i> <span>نمایش</span> model_data
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="hideAllJson()">
                    <i class="fas fa-times me-1"></i> مخفی کردن همه
                </button>
            </div>

            <div id="solutionJsonContainer" class="d-none">
                <h6 class="fw-bold mb-2"><i class="fas fa-database me-2"></i> solution_data</h6>
                <pre class="bg-light p-3 rounded border" style="max-height: 500px; overflow-y: auto;"><code><?= htmlspecialchars(json_encode($solutionData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></code></pre>
            </div>

            <div id="modelJsonContainer" class="d-none">
                <h6 class="fw-bold mb-2 mt-3"><i class="fas fa-database me-2"></i> model_data</h6>
                <pre class="bg-light p-3 rounded border" style="max-height: 500px; overflow-y: auto;"><code><?= htmlspecialchars(json_encode($modelData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></code></pre>
            </div>
        </div>
    </div>
</div>

<script>
function toggleJson(type) {
    const container = document.getElementById(type + 'JsonContainer');
    const btn = document.getElementById('toggle' + type.charAt(0).toUpperCase() + type.slice(1) + 'Btn');
    
    if (container.classList.contains('d-none')) {
        container.classList.remove('d-none');
        btn.querySelector('i').classList.replace('fa-eye', 'fa-eye-slash');
        btn.querySelector('span').textContent = 'مخفی کردن';
    } else {
        container.classList.add('d-none');
        btn.querySelector('i').classList.replace('fa-eye-slash', 'fa-eye');
        btn.querySelector('span').textContent = 'نمایش';
    }
}

function hideAllJson() {
    document.getElementById('solutionJsonContainer').classList.add('d-none');
    document.getElementById('modelJsonContainer').classList.add('d-none');
    
    document.getElementById('toggleSolutionBtn').querySelector('i').classList.replace('fa-eye-slash', 'fa-eye');
    document.getElementById('toggleSolutionBtn').querySelector('span').textContent = 'نمایش';
    
    document.getElementById('toggleModelBtn').querySelector('i').classList.replace('fa-eye-slash', 'fa-eye');
    document.getElementById('toggleModelBtn').querySelector('span').textContent = 'نمایش';
}
</script>