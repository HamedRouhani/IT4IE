<?php
$pageTitle = 'حوزه‌های دانشی - PMBOK';
$activePage = 'knowledgeArea';
?>

<div class="page-header">
    <div>
        <h2><i class="fas fa-sitemap"></i> حوزه‌های دانشی PMBOK</h2>
        <p class="text-muted">
            <?= $stats['total_ka'] ?? 0 ?> حوزه دانشی |
            <?= $stats['total_tasks'] ?? 0 ?> فرآیند |
            <?= $stats['total_techniques'] ?? 0 ?> تکنیک
        </p>
    </div>
</div>

<div class="ka-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
    <?php if (empty($knowledgeAreas)): ?>
        <div class="card">
            <p class="text-muted">حوزه دانشی ثبت نشده است.</p>
        </div>
    <?php else: ?>
        <?php foreach ($knowledgeAreas as $ka): ?>
            <div class="card ka-card">
                <div class="ka-header">
                    <h3 style="margin: 0; font-size: 1.3rem; color: var(--soft-primary);">
                        <?= htmlspecialchars($ka['name']) ?>
                    </h3>
                    <?php if (!empty($ka['code'])): ?>
                        <code class="ka-code"><?= htmlspecialchars($ka['code']) ?></code>
                    <?php endif; ?>
                </div>
                
                <p class="text-muted" style="margin: 10px 0; line-height: 1.7;">
                    <?= pmbok_truncateText($ka['description'] ?? '', 120) ?>
                </p>
                
                <div class="ka-stats">
                    <div class="ka-stat">
                        <i class="fas fa-tasks"></i>
                        <span><strong><?= $ka['task_count'] ?? 0 ?></strong> فرآیند</span>
                    </div>
                    <div class="ka-stat">
                        <i class="fas fa-microchip"></i>
                        <span><strong><?= $ka['technique_count'] ?? 0 ?></strong> تکنیک</span>
                    </div>
                </div>
                
                <a href="?controller=knowledgeArea&action=show&id=<?= $ka['id'] ?>" class="btn btn-primary btn-sm" style="margin-top: 15px; width: 100%;">
                    <i class="fas fa-arrow-left"></i> مشاهده جزئیات
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>