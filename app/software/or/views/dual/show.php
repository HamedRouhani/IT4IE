<?php
$project = $project ?? [];
$result = $result ?? [];
$c = $c ?? [];
$A = $A ?? [];
$b = $b ?? [];
$constraints_types = $constraints_types ?? [];
$ok = ($result['status'] ?? '') === 'optimal';
?>
<div class="container-fluid py-3 py-md-4">
    <div class="or-page-header">
        <div>
            <h3 class="mb-1"><i class="fas fa-balance-scale-right text-warning me-2"></i><?= or_e($project['name']) ?></h3>
            <div class="d-flex flex-wrap gap-1">
                <span class="badge bg-<?= $ok ? 'success' : 'danger' ?>"><?= $ok ? 'حل‌شده' : 'خطا' ?></span>
                <span class="badge bg-info"><?= strtoupper($project['sense']) ?></span>
                <small class="text-muted align-self-center">آخرین به‌روزرسانی: <?= or_e($project['updated_at']) ?></small>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="if(confirm('آیا از حذف این پروژه مطمئن هستید؟')) location.href='<?= or_url('controller=dual&action=delete&id=' . (int)$project['id']) ?>'">
                <i class="fas fa-trash me-1"></i><span class="d-none d-md-inline">حذف</span>
            </button>
            <a href="<?= or_url('controller=dual') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-right me-1"></i><span class="d-none d-md-inline">بازگشت به لیست</span>
            </a>
        </div>
    </div>

    <?php if (!$ok): ?>
        <div class="alert alert-danger">
            <h6><i class="fas fa-exclamation-triangle me-2"></i>خطا در حل مسئله</h6>
            <p class="mb-0 small"><?= or_e($result['message'] ?? 'نتیجه معتبری برای این پروژه ذخیره نشده است.') ?></p>
        </div>
    <?php else: ?>

        <div class="alert alert-success py-3 mb-3">
            <h6 class="fw-bold mb-2"><i class="fas fa-lightbulb me-2"></i>تفسیر نتیجه</h6>
            <pre class="mb-0 small" style="white-space: pre-wrap; line-height: 1.7;"><?= or_e($result['interpretation']) ?></pre>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2 py-md-3">
                <h6 class="mb-0"><i class="fas fa-table me-2 text-primary"></i>ماتریس محدودیت‌ها و ضرایب</h6>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="row g-3">
                    <div class="col-12">
                        <h6 class="fw-bold small mb-2 text-primary">تابع هدف (<?= strtoupper($result['sense']) ?> Z)</h6>
                        <div class="alert alert-light py-2">
                            <code class="d-block text-center">
                                Z = 
                                <?php 
                                $terms = [];
                                foreach ($c as $j => $val) {
                                    $sign = $val >= 0 && !empty($terms) ? '+' : '';
                                    $terms[] = "$sign " . number_format((float)$val, 2) . "x" . ($j + 1);
                                }
                                echo implode(' ', $terms);
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
                                        <th>محدودیت</th>
                                        <?php for ($j = 0; $j < count($c); $j++): ?>
                                            <th>x<?= $j + 1 ?></th>
                                        <?php endfor; ?>
                                        <th>علامت</th>
                                        <th>مقدار (RHS)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($A as $i => $row): ?>
                                    <tr>
                                        <td class="fw-bold">محدودیت <?= $i + 1 ?></td>
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
                <h6 class="mb-0"><i class="fas fa-chart-pie me-2 text-warning"></i>نتایج بهینه و تحلیل اقتصادی</h6>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="row g-1 g-md-2 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="border rounded bg-light text-center p-2 h-100">
                            <small class="d-block text-muted mb-1">مقدار تابع هدف</small>
                            <strong class="d-block fw-bold text-primary"><?= number_format((float)$result['objective_value'], 4) ?></strong>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded bg-light text-center p-2 h-100">
                            <small class="d-block text-muted mb-1">وضعیت</small>
                            <strong class="d-block text-success">بهینه (Optimal)</strong>
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
                            <small class="d-block text-muted mb-1">تعداد تکرار</small>
                            <strong class="d-block"><?= $result['iterations'] ?? 'N/A' ?></strong>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold small mb-2 mt-4"><i class="fas fa-calculator me-1 text-info"></i>مقادیر بهینه متغیرهای تصمیم</h6>
                <div class="row g-1 g-md-2 mb-3">
                    <?php foreach ($result['solution'] as $j => $val): ?>
                        <div class="col-6 col-md-3">
                            <div class="border rounded bg-light text-center p-2 h-100">
                                <small class="d-block text-muted mb-1">x<?= $j + 1 ?></small>
                                <strong class="d-block <?= abs($val) > 0.001 ? 'text-success fw-bold' : 'text-muted' ?>"><?= number_format((float)$val, 4) ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h6 class="fw-bold small mb-2 mt-4"><i class="fas fa-coins me-1 text-warning"></i>قیمت‌های سایه‌ای (Shadow Prices / Dual Values)</h6>
                <div class="row g-1 g-md-2 mb-3">
                    <?php foreach ($result['shadow_prices'] as $i => $price): ?>
                        <div class="col-6 col-md-3">
                            <div class="border rounded bg-light text-center p-2 h-100">
                                <small class="d-block text-muted mb-1">محدودیت <?= $i + 1 ?></small>
                                <strong class="d-block <?= abs($price) > 0.001 ? 'text-danger fw-bold' : 'text-muted' ?>"><?= number_format((float)$price, 4) ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <small class="text-muted d-block mb-3">* نشان‌دهنده ارزش نهایی یک واحد اضافی از هر منبع (RHS) است.</small>

                <h6 class="fw-bold small mb-2 mt-4"><i class="fas fa-arrow-down me-1 text-info"></i>هزینه‌های کاهش‌یافته (Reduced Costs)</h6>
                <div class="row g-1 g-md-2 mb-3">
                    <?php foreach ($result['reduced_costs'] as $j => $cost): ?>
                        <div class="col-6 col-md-3">
                            <div class="border rounded bg-light text-center p-2 h-100">
                                <small class="d-block text-muted mb-1">x<?= $j + 1 ?></small>
                                <strong class="d-block <?= abs($cost) > 0.001 ? 'text-warning fw-bold' : 'text-muted' ?>"><?= number_format((float)$cost, 4) ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <small class="text-muted d-block">* نشان‌دهنده میزان بهبود لازم در ضریب تابع هدف برای تولید آن متغیر است.</small>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2 py-md-3">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2 text-info"></i>اطلاعات پروژه</h6>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <table class="table table-borderless align-middle mb-0 small">
                            <tr>
                                <td class="fw-bold" style="width:40%;">شناسه پروژه</td>
                                <td class="font-monospace">#<?= (int)$project['id'] ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">تعداد متغیرهای تصمیم</td>
                                <td><?= count($c) ?> مورد</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">تعداد محدودیت‌ها</td>
                                <td><?= count($b) ?> مورد</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-12 col-md-6">
                        <table class="table table-borderless align-middle mb-0 small">
                            <tr>
                                <td class="fw-bold" style="width:40%;">تاریخ ایجاد</td>
                                <td><?= or_e($project['created_at']) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">آخرین به‌روزرسانی</td>
                                <td><?= or_e($project['updated_at']) ?></td>
                            </tr>
                            <?php if (!empty($project['description'])): ?>
                            <tr>
                                <td class="fw-bold">توضیحات</td>
                                <td><?= nl2br(or_e($project['description'])) ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>