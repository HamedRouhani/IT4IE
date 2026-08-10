<?php
/**
 * ویو لیست حوزه‌های دانشی BABOK
 * مسیر: app/software/babok/views/knowledge-areas/index.php
 */
$pageTitle = 'حوزه‌های دانشی BABOK - BABOK Analyzer';
$activePage = 'knowledge_areas';
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-sitemap"></i> حوزه‌های دانشی BABOK v3 (<?= count($knowledgeAreas) ?>)
        </div>
    </div>
    
    <?php if (empty($knowledgeAreas)): ?>
        <div class="text-muted text-center" style="padding: 40px 0;">
            <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
            <p style="margin-top: 10px;">هیچ حوزه دانشی یافت نشد.</p>
        </div>
    <?php else: ?>
        <div class="row" style="grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));">
            <?php foreach ($knowledgeAreas as $area): ?>
                <div class="card" style="margin-bottom: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                        <div>
                            <span class="badge badge-primary" style="font-size: 0.9rem; padding: 5px 12px;">
                                <?= htmlspecialchars($area['code']) ?>
                            </span>
                            <h4 style="margin-top: 10px; font-size: 1.1rem;"><?= htmlspecialchars($area['name']) ?></h4>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 2rem; font-weight: 700; color: var(--soft-secondary);">
                                <?= $area['task_count'] ?? 0 ?>
                            </div>
                            <div class="text-muted" style="font-size: 0.75rem;">وظیفه</div>
                        </div>
                    </div>
                    
                    <?php if (!empty($area['description'])): ?>
                        <p class="text-muted" style="font-size: 0.85rem; line-height: 1.6; margin-bottom: 15px;">
                            <?= mb_substr(htmlspecialchars($area['description']), 0, 120) ?><?= mb_strlen($area['description']) > 120 ? '...' : '' ?>
                        </p>
                    <?php endif; ?>
                    
                    <a href="?route=knowledge_areas_view&id=<?= $area['id'] ?>" class="btn btn-primary">
                        <i class="fas fa-eye"></i> مشاهده جزئیات و وظایف
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- نمودار توزیع وظایف -->
<div class="card" style="margin-top: 20px; background: #f8f9fa;">
    <h4 class="card-title"><i class="fas fa-chart-bar"></i> توزیع وظایف در حوزه‌های دانشی</h4>
    <div style="margin-top: 20px;">
        <?php 
        $maxTasks = max(array_column($knowledgeAreas, 'task_count'));
        foreach ($knowledgeAreas as $area): 
            $percentage = $maxTasks > 0 ? round(($area['task_count'] / $maxTasks) * 100) : 0;
        ?>
            <div style="margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span style="font-size: 0.85rem;"><?= htmlspecialchars($area['name']) ?></span>
                    <strong><?= $area['task_count'] ?> وظیفه</strong>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar" style="width: <?= $percentage ?>%; background: var(--soft-secondary);"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>