<?php
/**
 * ویو پیشنهاد تکنیک برای یک وظیفه
 * مسیر: app/software/babok/views/recommendations/task.php
 */
$pageTitle = 'پیشنهاد تکنیک: ' . $task['name'] . ' - BABOK Analyzer';
$activePage = 'tasks';

$categoryLabels = [
    'collaborative' => 'همکاری',
    'research' => 'تحقیقاتی',
    'experimental' => 'آزمایشی',
    'management' => 'مدیریتی',
    'strategic' => 'استراتژیک',
    'modeling' => 'مدل‌سازی'
];
?>

<!-- هدر -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-lightbulb"></i> پیشنهاد تکنیک هوشمند
        </div>
        <div class="card-tools">
            <a href="?route=tasks_view&id=<?= $task['id'] ?>" class="btn btn-primary">
                <i class="fas fa-tasks"></i> مشاهده وظیفه
            </a>
            <a href="?route=tasks" class="btn btn-secondary">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>
    
    <!-- اطلاعات وظیفه -->
    <div style="padding: 15px; background: #f0f7ff; border-radius: 8px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
            <span class="badge badge-secondary" style="font-size: 1rem; padding: 8px 15px;">
                <?= htmlspecialchars($task['code']) ?>
            </span>
            <h3 style="margin: 0;"><?= htmlspecialchars($task['name']) ?></h3>
        </div>
        <?php if (!empty($task['knowledge_area_code'])): ?>
            <p class="text-muted" style="margin: 10px 0 0 0;">
                <i class="fas fa-sitemap"></i> حوزه دانشی: <?= htmlspecialchars($task['knowledge_area_code']) ?>
            </p>
        <?php endif; ?>
        <?php if (!empty($task['description'])): ?>
            <p style="margin: 10px 0 0 0; line-height: 1.6;"><?= htmlspecialchars($task['description']) ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- تکنیک‌های پیشنهادی -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-star"></i> تکنیک‌های پیشنهادی (<?= count($recommendedTechniques) ?>)
        </div>
    </div>
    
    <?php if (empty($recommendedTechniques)): ?>
        <div class="text-muted text-center" style="padding: 40px 0;">
            <i class="fas fa-info-circle" style="font-size: 3rem; opacity: 0.3;"></i>
            <p style="margin-top: 15px;">هیچ پیشنهادی برای این وظیفه یافت نشد.</p>
        </div>
    <?php else: ?>
        <?php foreach ($recommendedTechniques as $index => $rec): ?>
            <?php 
            $tech = $rec['technique'] ?? $rec;
            $score = $rec['score'] ?? 0;
            $scorePercent = $rec['score_percent'] ?? 0;
            $reason = $rec['reason'] ?? '';
            ?>
            <div class="technique-card" style="border-right: 4px solid <?= $index < 3 ? 'var(--soft-success)' : 'var(--soft-secondary)' ?>;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px;">
                    <div style="flex: 1; min-width: 250px;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                            <span class="badge badge-<?= $index < 3 ? 'success' : 'primary' ?>" style="font-size: 0.8rem; padding: 5px 12px;">
                                پیشنهاد #<?= $index + 1 ?>
                            </span>
                            <span class="technique-category category-<?= $tech['category'] ?>">
                                <?= $categoryLabels[$tech['category']] ?? $tech['category'] ?>
                            </span>
                        </div>
                        <h4 style="margin: 0 0 8px 0;"><?= htmlspecialchars($tech['name']) ?></h4>
                        <?php if (!empty($tech['purpose'])): ?>
                            <p style="font-size: 0.85rem; color: #666; margin: 0; line-height: 1.6;">
                                <?= htmlspecialchars($tech['purpose']) ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($reason)): ?>
                            <div class="technique-reason">
                                <i class="fas fa-info-circle"></i> <?= htmlspecialchars($reason) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 2rem; font-weight: 700; color: var(--soft-secondary);">
                            <?= $scorePercent ?>%
                        </div>
                        <div class="text-muted" style="font-size: 0.75rem;">تطابق</div>
                        <div class="progress" style="width: 80px; margin-top: 8px;">
                            <div class="progress-bar" style="width: <?= $scorePercent ?>%;"></div>
                        </div>
                    </div>
                </div>
                <div style="margin-top: 12px;">
                    <a href="?route=techniques_view&id=<?= $tech['id'] ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> مشاهده جزئیات تکنیک
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- همه تکنیک‌ها -->
<details class="card">
    <summary style="cursor: pointer; font-weight: 600; font-size: 1.1rem;">
        <i class="fas fa-list"></i> مشاهده همه تکنیک‌ها (<?= count($allTechniques) ?>)
    </summary>
    <div style="margin-top: 20px;">
        <div class="techniques-grid">
            <?php foreach ($allTechniques as $tech): ?>
                <div class="technique-card" style="padding: 15px;">
                    <span class="technique-category category-<?= $tech['category'] ?>">
                        <?= $categoryLabels[$tech['category']] ?? $tech['category'] ?>
                    </span>
                    <h5 style="margin: 8px 0 5px 0; font-size: 0.9rem;"><?= htmlspecialchars($tech['name']) ?></h5>
                    <a href="?route=techniques_view&id=<?= $tech['id'] ?>" class="btn btn-sm btn-secondary">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</details>