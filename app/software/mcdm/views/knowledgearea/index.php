<?php
/**
 * لیست حوزه‌های دانشی MCDM
 */
?>

<div class="page-header">
    <div>
        <h2><i class="fas fa-brain"></i> حوزه‌های دانشی MCDM</h2>
        <div class="breadcrumb">
            <a href="<?= CURRENT_MODULE_URL ?>">داشبورد</a> / حوزه‌های دانشی
        </div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">🧠</div>
        <div>
            <div class="stat-value"><?= count($areas ?? []) ?></div>
            <div class="stat-label">حوزه دانشی</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📚</div>
        <div>
            <div class="stat-value"><?= array_sum(array_column($areas ?? [], 'method_count')) ?></div>
            <div class="stat-label">روش تصمیم‌گیری</div>
        </div>
    </div>
</div>

<div class="card">
    <h3 class="card-title">حوزه‌های دانشی تصمیم‌گیری چندمعیاره</h3>
    
    <div class="ka-grid">
        <?php if (empty($areas)): ?>
            <div class="empty-state">
                <i class="fas fa-info-circle"></i>
                <p>حوزه دانشی ثبت نشده است.</p>
            </div>
        <?php else: ?>
            <?php foreach ($areas as $area): ?>
            <div class="ka-card">
                <div class="ka-header">
                    <div>
                        <span class="ka-code"><?= htmlspecialchars($area['code'] ?? '') ?></span>
                        <h4 style="margin: 8px 0 4px;"><?= htmlspecialchars($area['name_fa'] ?? $area['name'] ?? '') ?></h4>
                    </div>
                </div>
                <p style="color: var(--soft-primary); font-size: 0.9rem; margin: 8px 0;">
                    <?= htmlspecialchars($area['name_en'] ?? '') ?>
                </p>
                <p style="font-size: 0.85rem; line-height: 1.6;">
                    <?= htmlspecialchars(mb_substr($area['description'] ?? '', 0, 150)) ?>...
                </p>
                <div class="ka-stats">
                    <div class="ka-stat">
                        <i class="fas fa-calculator"></i>
                        <span><?= (int)($area['method_count'] ?? 0) ?> روش</span>
                    </div>
                </div>
                <div style="margin-top: 12px;">
                    <a href="<?= CURRENT_MODULE_URL ?>?controller=knowledgearea&action=show&id=<?= $area['id'] ?>" 
                       class="btn btn-primary btn-sm">
                        مشاهده جزئیات <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>