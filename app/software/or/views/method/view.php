<?php
/**
 * نمایش جزئیات و آموزش یک روش حل
 * مسیر: app/software/or/views/method/view.php
 */
?>

<div class="container-fluid py-4">
    <!-- هدر صفحه -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-calculator text-primary"></i> <?= htmlspecialchars($method['name_fa']) ?>
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('') ?>">OR Analyzer</a></li>
                    <li class="breadcrumb-item"><a href="<?= or_url('controller=method') ?>">روش‌های حل</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($method['name_fa']) ?></li>
                </ol>
            </nav>
        </div>
        <a href="<?= or_url('controller=method') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right"></i> بازگشت به لیست
        </a>
    </div>

    <div class="row">
        <!-- ستون اصلی -->
        <div class="col-lg-8">
            <!-- اطلاعات پایه -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-info-circle text-info"></i> اطلاعات پایه</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>نام انگلیسی:</strong>
                            <span class="ms-2"><?= htmlspecialchars($method['name_en'] ?? '-') ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong>کد اختصاری:</strong>
                            <span class="badge bg-primary ms-2"><?= htmlspecialchars($method['code']) ?></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>نوع مسئله:</strong>
                            <span class="ms-2"><?= or_getProblemTypeLabel($method['problem_type_code'] ?? '') ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong>دسته‌بندی:</strong>
                            <span class="ms-2"><?= or_getMethodCategoryLabel($method['category'] ?? '') ?></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>توضیحات کلی:</strong>
                        <p class="mt-2 mb-0 text-muted"><?= nl2br(htmlspecialchars($method['description'] ?? 'توضیحاتی ثبت نشده است.')) ?></p>
                    </div>
                </div>
            </div>

            <!-- مراحل اجرایی -->
            <?php 
            $steps = !empty($method['steps']) ? json_decode($method['steps'], true) : null;
            if (is_array($steps) && count($steps) > 0): 
            ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-list-ol text-warning"></i> گام‌های اجرایی الگوریتم</h5>
                </div>
                <div class="card-body">
                    <div class="or-steps">
                        <?php foreach ($steps as $i => $step): ?>
                            <div class="or-algorithm-step mb-3">
                                <div class="d-flex align-items-start">
                                    <span class="step-number"><?= $i + 1 ?></span>
                                    <div>
                                        <strong><?= htmlspecialchars($step['title'] ?? 'گام ' . ($i + 1)) ?></strong>
                                        <p class="mb-0 mt-1 text-muted"><?= nl2br(htmlspecialchars($step['description'] ?? '')) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- فرمول‌های ریاضی -->
            <?php 
            $formulas = !empty($method['formulas']) ? json_decode($method['formulas'], true) : null;
            if (is_array($formulas) && count($formulas) > 0): 
            ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-square-root-alt text-danger"></i> فرمول‌های ریاضی</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($formulas as $formula): ?>
                        <div class="or-formula-card mb-3">
                            <div class="formula-title mb-2">
                                <strong><?= htmlspecialchars($formula['name'] ?? '') ?></strong>
                            </div>
                            <div class="formula-content bg-light p-3 rounded text-center font-monospace">
                                <?= htmlspecialchars($formula['text'] ?? '') ?>
                            </div>
                            <?php if (!empty($formula['description'])): ?>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-lightbulb"></i> <?= htmlspecialchars($formula['description']) ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ستون کناری -->
        <div class="col-lg-4">
            <!-- ورودی و خروجی -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0"><i class="fas fa-exchange-alt text-success"></i> ورودی و خروجی</h6>
                </div>
                <div class="card-body">
                    <?php 
                    $inputs = !empty($method['inputs']) ? json_decode($method['inputs'], true) : null;
                    if (is_array($inputs) && count($inputs) > 0): 
                    ?>
                        <h6 class="text-muted small mb-2">ورودی‌ها:</h6>
                        <ul class="list-unstyled mb-3">
                            <?php foreach ($inputs as $input): ?>
                                <li class="mb-2"><i class="fas fa-arrow-left text-primary"></i> <small><?= htmlspecialchars($input) ?></small></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php 
                    $outputs = !empty($method['outputs']) ? json_decode($method['outputs'], true) : null;
                    if (is_array($outputs) && count($outputs) > 0): 
                    ?>
                        <h6 class="text-muted small mb-2">خروجی‌ها:</h6>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($outputs as $output): ?>
                                <li class="mb-2"><i class="fas fa-arrow-right text-success"></i> <small><?= htmlspecialchars($output) ?></small></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    
                    <?php if (empty($inputs) && empty($outputs)): ?>
                        <p class="text-muted small mb-0">اطلاعات ورودی و خروجی برای این روش ثبت نشده است.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- مزایا و معایب -->
            <?php 
            $pros = !empty($method['pros']) ? json_decode($method['pros'], true) : null;
            $cons = !empty($method['cons']) ? json_decode($method['cons'], true) : null;
            if ((is_array($pros) && count($pros) > 0) || (is_array($cons) && count($cons) > 0)): 
            ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0"><i class="fas fa-balance-scale text-warning"></i> مزایا و معایب</h6>
                </div>
                <div class="card-body">
                    <?php if (is_array($pros) && count($pros) > 0): ?>
                        <h6 class="text-success small mb-2">✅ مزایا:</h6>
                        <ul class="list-unstyled mb-3">
                            <?php foreach ($pros as $pro): ?>
                                <li class="mb-1 small">✅ <?= htmlspecialchars($pro) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if (is_array($cons) && count($cons) > 0): ?>
                        <h6 class="text-danger small mb-2">⚠️ معایب:</h6>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($cons as $con): ?>
                                <li class="mb-1 small">⚠️ <?= htmlspecialchars($con) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- دکمه شروع پروژه -->
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="mb-3">آماده حل مسئله با این روش هستید؟</h6>
                    <a href="<?= or_url('controller=project&action=create') ?>" class="btn btn-or-success w-100">
                        <i class="fas fa-plus"></i> ایجاد پروژه جدید
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>