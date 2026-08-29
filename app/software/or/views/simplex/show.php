<?php
/**
 * نمایش جزئیات پروژه برنامه‌ریزی خطی (Simplex)
 * مسیر: app/software/or/views/simplex/show.php
 */
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-chart-line text-primary"></i> <?= or_e($project['name']) ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('controller=simplex') ?>">برنامه‌ریزی خطی</a></li>
                    <li class="breadcrumb-item active"><?= or_e($project['name']) ?></li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="<?= or_url('controller=simplex&action=edit&id=' . $project['id']) ?>" class="btn btn-outline-warning btn-sm">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <button class="btn btn-outline-danger btn-sm" onclick="deleteProject(<?= $project['id'] ?>, '<?= or_e($project['name']) ?>')">
                <i class="fas fa-trash"></i> حذف
            </button>
        </div>
    </div>

    <!-- تب‌ها -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'info' ? 'active' : '' ?>" href="<?= or_url('controller=simplex&action=show&id=' . $project['id'] . '&tab=info') ?>">
                <i class="fas fa-info-circle"></i> اطلاعات
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'nodes' ? 'active' : '' ?>" href="<?= or_url('controller=simplex&action=show&id=' . $project['id'] . '&tab=nodes') ?>">
                <i class="fas fa-list"></i> متغیرها و محدودیت‌ها
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'matrix' ? 'active' : '' ?>" href="<?= or_url('controller=simplex&action=show&id=' . $project['id'] . '&tab=matrix') ?>">
                <i class="fas fa-table"></i> ماتریس
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'solve' ? 'active' : '' ?>" href="<?= or_url('controller=simplex&action=show&id=' . $project['id'] . '&tab=solve') ?>">
                <i class="fas fa-play-circle"></i> حل مسئله
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'result' ? 'active' : '' ?>" href="<?= or_url('controller=simplex&action=show&id=' . $project['id'] . '&tab=result') ?>">
                <i class="fas fa-chart-bar"></i> نتایج
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <?php if ($tab === 'info'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">توضیحات پروژه</h5>
                    <p class="text-muted"><?= nl2br(or_e($project['description'] ?? 'توضیحاتی ثبت نشده است.')) ?></p>
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">هدف مسئله</small>
                                <strong class="<?= $project['objective'] === 'maximize' ? 'text-success' : 'text-danger' ?>">
                                    <?= $project['objective'] === 'maximize' ? 'بیشینه‌سازی (Maximize)' : 'کمینه‌سازی (Minimize)' ?>
                                </strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">تعداد متغیرها</small>
                                <strong class="text-primary"><?= count($modelData['c'] ?? []) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">تعداد محدودیت‌ها</small>
                                <strong class="text-info"><?= count($modelData['b'] ?? []) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($tab === 'nodes'): ?>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-primary text-white py-3">
                            <h6 class="mb-0"><i class="fas fa-variable"></i> ضرایب تابع هدف (C)</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>متغیر</th>
                                            <th>ضریب در تابع هدف</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($modelData['c'] as $i => $val): ?>
                                            <tr>
                                                <td>x<sub><?= $i + 1 ?></sub></td>
                                                <td><?= number_format($val, 4) ?></td>
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
                            <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> محدودیت‌ها (Constraints)</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>محدودیت</th>
                                            <th>علامت</th>
                                            <th>سمت راست (b)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($modelData['b'] as $i => $val): ?>
                                            <tr>
                                                <td>محدودیت <?= $i + 1 ?></td>
                                                <td><?= $modelData['types'][$i] ?></td>
                                                <td><?= number_format($val, 4) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($tab === 'matrix'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-table text-success"></i> ماتریس ضرایب محدودیت‌ها (A)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="or-matrix text-center">
                            <thead>
                                <tr>
                                    <th>محدودیت \ متغیر</th>
                                    <?php foreach ($modelData['c'] as $i => $val): ?>
                                        <th>x<sub><?= $i + 1 ?></sub></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($modelData['A'] as $i => $row): ?>
                                    <tr>
                                        <th class="supply-demand-cell">محدودیت <?= $i + 1 ?></th>
                                        <?php foreach ($row as $val): ?>
                                            <td><?= number_format($val, 4) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($tab === 'solve'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <h4 class="mb-3">آماده حل مسئله برنامه‌ریزی خطی</h4>
                    <p class="text-muted mb-4">
                        برای حل این مسئله از الگوریتم سیمپلکس دو مرحله‌ای (Two-Phase Simplex) استفاده خواهد شد.
                    </p>
                    <button class="btn btn-or-success btn-lg" onclick="runSimplexSolve(<?= $project['id'] ?>)">
                        <i class="fas fa-play"></i> اجرای الگوریتم سیمپلکس
                    </button>
                    <div id="solveLoader" class="d-none mt-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2">در حال حل مسئله... (این عملیات ممکن است چند لحظه طول بکشد)</p>
                    </div>
                </div>
            </div>

        <?php elseif ($tab === 'result'): ?>
            <?php 
            $solution = json_decode($project['solution_data'] ?? 'null', true);
            $modelData = json_decode($project['model_data'] ?? '{}', true);
            $optimalValue = $project['optimal_value'] ?? ($solution['optimal_value'] ?? 0);
            
            if ($project['status'] !== 'solved' || empty($solution) || ($solution['status'] ?? '') !== 'optimal'): 
            ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-hourglass-half fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted mb-3">هنوز مسئله حل نشده است</h4>
                        <p class="text-muted mb-4">
                            وضعیت فعلی: <strong><?= or_getStatusLabel($project['status'] ?? 'draft') ?></strong><br>
                            لطفاً به تب "حل مسئله" بروید و الگوریتم را اجرا کنید.
                        </p>
                        <a href="<?= or_url('controller=simplex&action=show&id=' . $project['id'] . '&tab=solve') ?>" class="btn btn-or-primary">
                            <i class="fas fa-play"></i> رفتن به تب حل مسئله
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- کارت‌های آماری -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="or-stat-card text-center">
                            <h6 class="text-muted mb-2">مقدار بهینه (Z)</h6>
                            <h3 class="mb-0 text-success fw-bold"><?= number_format($optimalValue, 4) ?></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="or-stat-card text-center">
                            <h6 class="text-muted mb-2">تعداد تکرار</h6>
                            <h3 class="mb-0 text-primary fw-bold"><?= $solution['iterations'] ?? 0 ?></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="or-stat-card text-center">
                            <h6 class="text-muted mb-2">وضعیت</h6>
                            <h4 class="mb-0 text-success fw-bold">بهینه (Optimal)</h4>
                        </div>
                    </div>
                </div>

                <!-- جواب بهینه -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0"><i class="fas fa-star text-warning"></i> مقادیر بهینه متغیرها</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($solution['solution']) && is_array($solution['solution'])): ?>
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
                        <?php else: ?>
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle"></i> مقادیر متغیرها در دسترس نیست.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- تحلیل حساسیت: قیمت‌های سایه‌ای -->
                <?php if (!empty($solution['shadow_prices'])): ?>
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
                                    <?php foreach ($solution['shadow_prices'] as $i => $price): ?>
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

                <!-- تحلیل حساسیت: محدوده تغییرات ضرایب هدف -->
                <?php if (!empty($solution['objective_ranges'])): ?>
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
                                    <?php foreach ($solution['objective_ranges'] as $range): 
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

                <!-- تحلیل حساسیت: محدوده تغییرات RHS -->
                <?php if (!empty($solution['rhs_ranges'])): ?>
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
                                    <?php foreach ($solution['rhs_ranges'] as $range): 
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

                <!-- دکمه‌های عملیات -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="<?= or_url('controller=simplex') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-list"></i> بازگشت به لیست
                    </a>
                    <div class="d-flex gap-2">
                        <a href="<?= or_url('controller=simplex&action=edit&id=' . $project['id']) ?>" class="btn btn-outline-warning">
                            <i class="fas fa-edit"></i> ویرایش مدل
                        </a>
                        <button class="btn btn-or-primary" onclick="window.print()">
                            <i class="fas fa-print"></i> چاپ گزارش
                        </button>
                    </div>
                </div>
            <?php endif; // پایان شرط داخلی (وضعیت حل) ?>
        <?php endif; // ✅ پایان شرط اصلی (تب‌ها) - این خط اضافه شده بود تا خطا رفع شود ?>
    </div>
</div>

<script>
async function runSimplexSolve(id) {
    const loader = document.getElementById('solveLoader');
    loader.classList.remove('d-none');
    
    try {
        const res = await fetch('<?= or_url("controller=simplex&action=solve&id=") ?>' + id, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'}
        });
        const data = await res.json();
        
        if (data.success) {
            alert('✅ ' + (data.message || 'مسئله با موفقیت حل شد!'));
            window.location.href = '<?= or_url("controller=simplex&action=show&id=") ?>' + id + '&tab=result';
        } else {
            alert('❌ خطا: ' + (data.error || 'خطای نامشخص در حل مسئله'));
        }
    } catch (e) {
        alert('❌ خطای شبکه: ' + e.message);
    } finally {
        loader.classList.add('d-none');
    }
}

function deleteProject(id, name) {
    if (confirm('آیا از حذف پروژه "' + name + '" مطمئن هستید؟ این عملیات غیرقابل بازگشت است.')) {
        window.location.href = '<?= or_url("controller=simplex&action=delete&id=") ?>' + id;
    }
}
</script>