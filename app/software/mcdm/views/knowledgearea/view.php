<?php
/**
 * نمایش جزئیات یک حوزه دانشی
 */
?>

<div class="page-header">
    <div>
        <h2><i class="fas fa-brain"></i> <?= htmlspecialchars($area['name_fa'] ?? $area['name'] ?? '') ?></h2>
        <div class="breadcrumb">
            <a href="<?= CURRENT_MODULE_URL ?>">داشبورد</a> / 
            <a href="<?= CURRENT_MODULE_URL ?>?controller=knowledgearea">حوزه‌های دانشی</a> / 
            <?= htmlspecialchars($area['code'] ?? '') ?>
        </div>
    </div>
    <a href="<?= CURRENT_MODULE_URL ?>?controller=knowledgearea" class="btn btn-secondary">
        <i class="fas fa-arrow-right"></i> بازگشت
    </a>
</div>

<div class="card">
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">کد حوزه</span>
            <strong><?= htmlspecialchars($area['code'] ?? '') ?></strong>
        </div>
        <div class="info-item">
            <span class="info-label">نام فارسی</span>
            <strong><?= htmlspecialchars($area['name_fa'] ?? $area['name'] ?? '') ?></strong>
        </div>
        <div class="info-item">
            <span class="info-label">نام انگلیسی</span>
            <strong><?= htmlspecialchars($area['name_en'] ?? '-') ?></strong>
        </div>
        <div class="info-item">
            <span class="info-label">تعداد روش‌ها</span>
            <strong><?= count($methods ?? []) ?> روش</strong>
        </div>
    </div>
    
    <?php if (!empty($area['description'])): ?>
    <div style="margin-top: 20px;">
        <h4 style="margin-bottom: 10px;">توضیحات</h4>
        <p style="line-height: 1.8; color: #555;">
            <?= nl2br(htmlspecialchars($area['description'])) ?>
        </p>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($methods)): ?>
<div class="card">
    <h3 class="card-title">
        <i class="fas fa-calculator"></i>
        روش‌های این حوزه (<?= count($methods) ?>)
    </h3>
    
    <div class="techniques-grid">
        <?php foreach ($methods as $method): ?>
        <div class="technique-card" onclick="window.location='<?= CURRENT_MODULE_URL ?>?controller=method&action=show&id=<?= $method['id'] ?>'">
            <div class="technique-icon">
                <i class="fas fa-calculator"></i>
            </div>
            <div class="technique-info">
                <h4><?= htmlspecialchars($method['name_fa'] ?? $method['name'] ?? $method['name_en'] ?? '') ?></h4>
                <p style="font-size: 0.8rem; color: var(--soft-primary);">
                    <?= htmlspecialchars($method['code'] ?? '') ?>
                </p>
                <p style="font-size: 0.85rem; margin-top: 4px;">
                    <?= htmlspecialchars(mb_substr($method['description'] ?? '', 0, 80)) ?>...
                </p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="empty-state">
        <i class="fas fa-info-circle"></i>
        <p>هنوز روشی برای این حوزه ثبت نشده است.</p>
    </div>
</div>
<?php endif; ?>