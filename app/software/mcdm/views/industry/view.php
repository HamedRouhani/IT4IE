<div class="page-header">
    <div>
        <h2><i class="fas fa-industry"></i> <?= htmlspecialchars($industry['name_fa'] ?? '') ?></h2>
        <div class="breadcrumb">
            <a href="<?= mcdm_url('controller=dashboard') ?>">داشبورد</a> / 
            <a href="<?= mcdm_url('controller=industry') ?>">صنایع</a> / 
            <?= htmlspecialchars($industry['name_fa'] ?? '') ?>
        </div>
    </div>
    <a href="<?= mcdm_url('controller=industry') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-right"></i> بازگشت
    </a>
</div>

<div class="card">
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">کد صنعت</span>
            <strong><?= htmlspecialchars($industry['code'] ?? '') ?></strong>
        </div>
        <div class="info-item">
            <span class="info-label">نام فارسی</span>
            <strong><?= htmlspecialchars($industry['name_fa'] ?? '') ?></strong>
        </div>
        <div class="info-item">
            <span class="info-label">نام انگلیسی</span>
            <strong><?= htmlspecialchars($industry['name_en'] ?? '-') ?></strong>
        </div>
    </div>
    
    <?php if (!empty($industry['description'])): ?>
    <div style="margin-top: 20px;">
        <h4 style="margin-bottom: 10px;">توضیحات</h4>
        <p style="line-height: 1.8;"><?= nl2br(htmlspecialchars($industry['description'])) ?></p>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($industry['characteristics'])): ?>
    <div style="margin-top: 20px;">
        <h4 style="margin-bottom: 10px;">ویژگی‌ها</h4>
        <p style="line-height: 1.8;"><?= nl2br(htmlspecialchars($industry['characteristics'])) ?></p>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($industry['common_decisions'])): ?>
    <div style="margin-top: 20px;">
        <h4 style="margin-bottom: 10px;">تصمیمات رایج</h4>
        <p style="line-height: 1.8;"><?= nl2br(htmlspecialchars($industry['common_decisions'])) ?></p>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($recommendedMethods)): ?>
<div class="card">
    <h3 class="card-title">
        <i class="fas fa-star"></i>
        روش‌های پیشنهادی برای این صنعت
    </h3>
    
    <div class="techniques-grid">
        <?php foreach ($recommendedMethods as $method): ?>
        <div class="technique-card" onclick="window.location='<?= mcdm_url('controller=method&action=show&id=' . $method['id']) ?>'">
            <div class="technique-icon">
                <i class="fas fa-calculator"></i>
            </div>
            <div class="technique-info">
                <h4><?= htmlspecialchars($method['name_fa'] ?? $method['name'] ?? '') ?></h4>
                <p style="font-size: 0.8rem; color: var(--soft-primary);">
                    امتیاز مرتبط بودن: <?= $method['relevance_score'] ?? 0 ?>/10
                </p>
                <?php if (!empty($method['priority'])): ?>
                <span class="badge badge-<?= $method['priority'] === 'critical' ? 'danger' : ($method['priority'] === 'high' ? 'warning' : 'info') ?>">
                    <?= $method['priority'] === 'critical' ? 'حیاتی' : ($method['priority'] === 'high' ? 'بالا' : 'متوسط') ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>