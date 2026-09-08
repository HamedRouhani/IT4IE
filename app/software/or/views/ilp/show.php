<?php
$project = $project ?? [];
$result = $result ?? [];
$c = $c ?? [];
$A = $A ?? [];
$b = $b ?? [];
$constraints_types = $constraints_types ?? [];
$integer_vars = $integer_vars ?? [];
$ok = ($result['status'] ?? '') === 'optimal';
?>
<div class="container-fluid py-3 py-md-4">
    <div class="or-page-header">
        <div>
            <h3 class="mb-1"><i class="fas fa-cubes text-danger me-2"></i><?= or_e($project['name']) ?></h3>
            <div class="d-flex flex-wrap gap-1">
                <span class="badge bg-<?= $ok ? 'success' : 'danger' ?>"><?= $ok ? 'حل‌شده' : 'خطا' ?></span>
                <span class="badge bg-danger">ILP</span>
                <span class="badge bg-info"><?= strtoupper($project['sense']) ?></span>
                <small class="text-muted align-self-center"><?= or_e($project['updated_at']) ?></small>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= or_url('controller=ilp&action=edit&id=' . (int)$project['id']) ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-edit me-1"></i><span class="d-none d-md-inline">ویرایش</span>
            </a>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="if(confirm('آیا از حذف این پروژه مطمئن هستید؟')) location.href='<?= or_url('controller=ilp&action=delete&id=' . (int)$project['id']) ?>'">
                <i class="fas fa-trash me-1"></i><span class="d-none d-md-inline">حذف</span>
            </button>
            <a href="<?= or_url('controller=ilp') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-right me-1"></i><span class="d-none d-md-inline">بازگشت به لیست</span>
            </a>
        </div>
    </div>

    <?php if (!$ok): ?>
        <div class="alert alert-danger">
            <h6><i class="fas fa-exclamation-triangle me-2"></i>خطا</h6>
            <p class="mb-0 small"><?= or_e($result['message'] ?? 'نتیجه معتبری موجود نیست.') ?></p>
        </div>
    <?php else: ?>

        <div class="alert alert-success py-3 mb-3">
            <h6 class="fw-bold mb-2"><i class="fas fa-lightbulb me-2"></i>تفسیر نتیجه</h6>
            <pre class="mb-0 small" style="white-space: pre-wrap; line-height: 1.7;"><?= or_e($result['interpretation']) ?></pre>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2 py-md-3">
                <h6 class="mb-0"><i class="fas fa-table me-2 text-primary"></i>فرمول‌بندی مسئله</h6>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="row g-3">
                    <div class="col-12">
                        <h6 class="fw-bold small mb-2 text-primary">تابع هدف</h6>
                        <div class="alert alert-light py-2">
                            <code class="d-block text-center" style="font-size: 1.1rem; direction: ltr; text-align: center;">
                                <?= strtoupper($result['sense']) ?> Z = 
                                <?php 
                                $terms = [];
                                foreach ($c as $j => $val): 
                                    $val = (float)$val;
                                    if (abs($val) < 0.0001) continue; // ضرایب صفر را رد کن
                                    
                                    $sign = ($val > 0 && !empty($terms)) ? ' + ' : '';
                                    if ($val < 0) {
                                        $sign = !empty($terms) ? ' - ' : '-';
                                        $val = abs($val);
                                    }
                                    
                                    $intMark = in_array($j, $integer_vars) ? ' <small class="text-danger">(صحیح)</small>' : '';
                                    $terms[] = "{$sign}" . number_format($val, 2) . "x" . ($j + 1) . $intMark;
                                endforeach;
                                echo implode('', $terms);
                                ?>
                            </code>
                        </div>
                    </div>

                    <div class="col-12">
                        <h6 class="fw-bold small mb-2 text-success">محدودیت‌ها</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered text-center mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <?php for ($j = 0; $j < count($c); $j++): ?>
                                            <th>x<?= $j + 1 ?></th>
                                        <?php endfor; ?>
                                        <th>علامت</th>
                                        <th>RHS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($A as $i => $row): ?>
                                    <tr>
                                        <td class="fw-bold"><?= $i + 1 ?></td>
                                        <?php foreach ($row as $val): ?>
                                            <td class="font-monospace"><?= number_format((float)$val, 2) ?></td>
                                        <?php endforeach; ?>
                                        <td><?= or_e($constraints_types[$i] ?? '<=') ?></td>
                                        <td class="fw-bold text-primary"><?= number_format((float)$b[$i], 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2 py-md-3">
                <h6 class="mb-0"><i class="fas fa-trophy me-2 text-danger"></i>جواب بهینه عدد صحیح</h6>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="row g-1 g-md-2 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="border rounded bg-light text-center p-2 h-100">
                            <small class="d-block text-muted mb-1">مقدار تابع هدف</small>
                            <strong class="d-block fw-bold text-danger"><?= number_format((float)$result['objective_value'], 4) ?></strong>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded bg-light text-center p-2 h-100">
                            <small class="d-block text-muted mb-1">وضعیت</small>
                            <strong class="d-block text-success">بهینه ILP</strong>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded bg-light text-center p-2 h-100">
                            <small class="d-block text-muted mb-1">نوع</small>
                            <strong class="d-block"><?= strtoupper($result['sense']) ?></strong>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded bg-light text-center p-2 h-100">
                            <small class="d-block text-muted mb-1">گره‌های بررسی‌شده</small>
                            <strong class="d-block"><?= $result['nodes_explored'] ?? 0 ?></strong>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold small mb-2 mt-4"><i class="fas fa-calculator me-1 text-info"></i>مقادیر بهینه متغیرها</h6>
                <div class="row g-1 g-md-2">
                    <?php foreach ($result['solution'] as $j => $val): ?>
                        <div class="col-6 col-md-3">
                            <div class="border rounded bg-light text-center p-2 h-100">
                                <small class="d-block text-muted mb-1">
                                    x<?= $j + 1 ?>
                                    <?php if (in_array($j, $integer_vars)): ?>
                                        <span class="badge bg-danger" style="font-size:.6rem;">صحیح</span>
                                    <?php endif; ?>
                                </small>
                                <strong class="d-block <?= in_array($j, $integer_vars) ? 'text-success fw-bold' : 'text-muted' ?>"><?= number_format((float)$val, 4) ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($result['branching_log'])): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2 py-md-3">
                <h6 class="mb-0"><i class="fas fa-project-diagram me-2 text-warning"></i>لاگ الگوریتم Branch & Bound</h6>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="table-responsive">
                    <table class="table table-sm table-striped small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>گره</th>
                                <th>عمق</th>
                                <th>رویداد / توضیح</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result['branching_log'] as $log): ?>
                            <tr>
                                <td class="fw-bold"><?= $log['node'] ?? '-' ?></td>
                                <td><?= $log['depth'] ?? '-' ?></td>
                                <td>
                                    <?php if (!empty($log['branch_var'])): ?>
                                        <span class="badge bg-warning text-dark">شاخه‌سازی: <?= or_e($log['branch_var']) ?> = <?= number_format((float)$log['branch_value'], 3) ?></span>
                                        <br><small>چپ: <?= or_e($log['left']) ?> | راست: <?= or_e($log['right']) ?></small>
                                    <?php else: ?>
                                        <?= or_e($log['message'] ?? '') ?>
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

    <?php endif; ?>
</div>