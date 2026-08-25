<?php
/**
 * نمایش جزئیات یک روش تصمیم‌گیری (MCDM Method View)
 */
$title = $method['name_fa'] ?? $method['name'] ?? 'جزئیات روش';
?>

<!-- هدر صفحه با دکمه بازگشت -->
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-calculator"></i> <?= mcdm_e($method['name_fa'] ?? $method['name']) ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= mcdm_url('controller=dashboard') ?>">داشبورد</a></li>
                <li class="breadcrumb-item"><a href="<?= mcdm_url('controller=method') ?>">روش‌های تصمیم‌گیری</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= mcdm_e($method['code'] ?? '') ?></li>
            </ol>
        </nav>
    </div>
    <a href="<?= mcdm_url('controller=method') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-right"></i> بازگشت به لیست
    </a>
</div>

<!-- کارت اصلی اطلاعات روش -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-book-open me-2"></i> راهنمای جامع روش</span>
        <span class="badge bg-warning"><?= mcdm_getMethodCategoryLabel($method['category'] ?? '') ?></span>
    </div>
    <div class="card-body">
        <h3 class="h4 mb-3" style="color: var(--mcdm-olive-900);">
            <?= mcdm_e($method['name_fa'] ?? $method['name']) ?>
            <small class="text-muted d-block mt-2" style="font-size: 0.9rem; direction: ltr; text-align: left;">
                <?= mcdm_e($method['name_en'] ?? '') ?> | Code: <?= mcdm_e($method['code'] ?? '') ?>
            </small>
        </h3>

        <!-- توضیحات آموزشی غنی -->
        <div style="white-space: pre-line; line-height: 1.9; color: #444; font-size: 1rem; margin-bottom: 24px;">
            <?= nl2br(mcdm_e($method['description'] ?? '')) ?>
        </div>

        <!-- مبانی ریاضی -->
        <?php if (!empty($method['mathematical_basis'])): ?>
        <div class="p-4" style="background: var(--mcdm-olive-50); border-radius: 10px; border: 1px dashed var(--mcdm-olive-700); direction: rtl; text-align: right;">
            <h5 style="font-size: 0.95rem; color: var(--mcdm-olive-700); margin-bottom: 12px; font-weight: 700;">
                <i class="fas fa-square-root-alt me-2"></i> مبانی ریاضی و فرمول‌های کلیدی:
            </h5>
            <p style="font-family: 'Vazirmatn', monospace; font-size: 0.95rem; color: var(--mcdm-olive-900); margin: 0; white-space: pre-wrap; line-height: 1.8;">
                <?= mcdm_e($method['mathematical_basis']) ?>
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- کارت گام‌های اجرایی -->
<?php if (!empty($steps)): ?>
<div class="card">
    <div class="card-header">
        <i class="fas fa-list-ol"></i>
        <span>گام‌های اجرایی روش (<?= count($steps) ?> مرحله)</span>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <?php foreach ($steps as $index => $step): ?>
            <div class="list-group-item p-4" style="border-bottom: 1px solid var(--mcdm-olive-100, #E8EDE0);">
                <div class="d-flex align-items-start" style="direction: rtl; text-align: right;">
                    <!-- شماره گام -->
                    <div class="flex-shrink-0">
                        <div class="step-number">
                            <?= $index + 1 ?>
                        </div>
                    </div>
                    
                    <!-- محتوای گام -->
                    <div class="step-content">
                        <h5 class="step-title">
                            <?= mcdm_e($step['name'] ?? '') ?>
                            <?php if (!empty($step['code'])): ?>
                                <span class="step-code"><?= mcdm_e($step['code']) ?></span>
                            <?php endif; ?>
                        </h5>
                        
                        <p class="step-description">
                            <?= nl2br(mcdm_e($step['description'] ?? '')) ?>
                        </p>
                        
                        <!-- برچسب‌های ورودی و خروجی -->
                        <div class="step-badges">
                            <?php if (!empty($step['inputs'])): ?>
                            <span class="badge-input">
                                <i class="fas fa-sign-in-alt"></i>
                                ورودی: <?= mcdm_e($step['inputs']) ?>
                            </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($step['outputs'])): ?>
                            <span class="badge-output">
                                <i class="fas fa-sign-out-alt"></i>
                                خروجی: <?= mcdm_e($step['outputs']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body text-center text-muted py-5">
        <i class="fas fa-info-circle" style="font-size: 2.5rem; margin-bottom: 15px; opacity: 0.4; color: var(--mcdm-olive-700);"></i>
        <p class="h5">گام‌های اجرایی برای این روش هنوز تعریف نشده است.</p>
    </div>
</div>
<?php endif; ?>

<!-- دکمه اقدام سریع -->
<div class="text-center mt-5 mb-5">
    <a href="<?= mcdm_url('controller=project&action=create') ?>" class="btn btn-primary btn-lg" style="padding: 14px 40px; font-size: 1.1rem; border-radius: 10px;">
        <i class="fas fa-plus-circle me-2"></i> شروع یک پروژه جدید با این روش
    </a>
</div>