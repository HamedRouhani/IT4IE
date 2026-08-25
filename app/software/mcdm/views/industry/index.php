<div class="page-header">
    <div>
        <h2><i class="fas fa-industry"></i> صنایع و کاربردها</h2>
        <div class="breadcrumb">
            <a href="<?= mcdm_url('controller=dashboard') ?>">داشبورد</a> / صنایع
        </div>
    </div>
</div>

<div class="card">
    <h3 class="card-title">کاربردهای MCDM در صنایع مختلف</h3>
    
    <div class="ka-grid">
        <?php if (empty($industries)): ?>
            <div class="empty-state">
                <i class="fas fa-info-circle"></i>
                <p>صنعتی ثبت نشده است.</p>
            </div>
        <?php else: ?>
            <?php foreach ($industries as $industry): ?>
            <div class="ka-card">
                <div class="ka-header">
                    <div>
                        <span class="ka-code"><?= htmlspecialchars($industry['code'] ?? '') ?></span>
                        <h4 style="margin: 8px 0 4px;"><?= htmlspecialchars($industry['name_fa'] ?? '') ?></h4>
                    </div>
                </div>
                <p style="color: var(--soft-primary); font-size: 0.9rem; margin: 8px 0;">
                    <?= htmlspecialchars($industry['name_en'] ?? '') ?>
                </p>
                <p style="font-size: 0.85rem; line-height: 1.6;">
                    <?= htmlspecialchars(mb_substr($industry['description'] ?? '', 0, 150)) ?>...
                </p>
                <div style="margin-top: 12px;">
                    <a href="<?= mcdm_url('controller=industry&action=show&id=' . $industry['id']) ?>" 
                       class="btn btn-primary btn-sm">
                        مشاهده جزئیات <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>