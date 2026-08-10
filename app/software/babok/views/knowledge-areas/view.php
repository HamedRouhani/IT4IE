<?php
/**
 * ویو مشاهده جزئیات حوزه دانشی
 * مسیر: app/software/babok/views/knowledge-areas/view.php
 */
$pageTitle = $knowledgeArea['name'] . ' - BABOK Analyzer';
$activePage = 'knowledge_areas';
?>

<!-- هدر حوزه دانشی -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-sitemap"></i>
            <span class="badge badge-primary" style="font-size: 0.9rem; padding: 5px 12px;">
                <?= htmlspecialchars($knowledgeArea['code']) ?>
            </span>
            <?= htmlspecialchars($knowledgeArea['name']) ?>
        </div>
        <div class="card-tools">
            <a href="?route=knowledge_areas" class="btn btn-secondary">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>
    
    <?php if (!empty($knowledgeArea['description'])): ?>
        <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; margin-bottom: 20px;">
            <p style="margin: 0; line-height: 1.8;"><?= nl2br(htmlspecialchars($knowledgeArea['description'])) ?></p>
        </div>
    <?php endif; ?>
    
    <!-- آمار -->
    <div class="d-flex gap-2 flex-wrap">
        <div style="padding: 12px 20px; background: #e8f4fd; border-radius: 8px; text-align: center;">
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--soft-secondary);"><?= count($tasks) ?></div>
            <div class="text-muted" style="font-size: 0.8rem;">وظیفه</div>
        </div>
        <div style="padding: 12px 20px; background: #e8f8f0; border-radius: 8px; text-align: center;">
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--soft-success);">
                <?php
                $totalTechniques = 0;
                foreach ($tasks as $task) {
                    if (!empty($task['techniques'])) {
                        $totalTechniques += count(explode(', ', $task['techniques']));
                    }
                }
                echo $totalTechniques;
                ?>
            </div>
            <div class="text-muted" style="font-size: 0.8rem;">تکنیک مرتبط</div>
        </div>
    </div>
</div>

<!-- لیست وظایف حوزه -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-tasks"></i> وظایف این حوزه (<?= count($tasks) ?>)
        </div>
    </div>
    
    <?php if (empty($tasks)): ?>
        <p class="text-muted">هیچ وظیفه‌ای در این حوزه تعریف نشده است.</p>
    <?php else: ?>
        <?php foreach ($tasks as $task): ?>
            <div class="card" style="border-right: 4px solid var(--soft-secondary); margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px;">
                    <div style="flex: 1; min-width: 250px;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                            <span class="badge badge-secondary"><?= htmlspecialchars($task['code']) ?></span>
                            <h4 style="margin: 0; font-size: 1rem;"><?= htmlspecialchars($task['name']) ?></h4>
                        </div>
                        <?php if (!empty($task['description'])): ?>
                            <p class="text-muted" style="font-size: 0.85rem; margin: 0; line-height: 1.6;">
                                <?= htmlspecialchars($task['description']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <a href="?route=tasks_view&id=<?= $task['id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i> جزئیات
                        </a>
                    </div>
                </div>
                
                <?php if (!empty($task['techniques'])): ?>
                    <div style="margin-top: 12px; padding-top: 12px; border-top: 1px dashed #e0e0e0;">
                        <strong style="font-size: 0.85rem; color: var(--soft-primary);">
                            <i class="fas fa-tools"></i> تکنیک‌ها:
                        </strong>
                        <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 8px;">
                            <?php foreach (explode(', ', $task['techniques']) as $techName): ?>
                                <span class="badge badge-secondary"><?= htmlspecialchars(trim($techName)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>