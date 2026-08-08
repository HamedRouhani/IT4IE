<?php
$pageTitle = 'پیشنهاد تکنیک‌ها - ' . htmlspecialchars($task['name']) . ' - BABOK Analyzer';
$activePage = 'tasks';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>
        <i class="fas fa-lightbulb" style="color: var(--warning-color);"></i>
        پیشنهاد تکنیک‌ها برای وظیفه
    </h2>
    <a href="/babok/public/?route=tasks" class="btn btn-secondary">
        <i class="fas fa-arrow-right"></i> بازگشت
    </a>
</div>

<!-- اطلاعات وظیفه -->
<div class="card mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>کد:</strong> <?= htmlspecialchars($task['code']) ?></p>
                <p><strong>نام:</strong> <?= htmlspecialchars($task['name']) ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>حوزه دانشی:</strong> 
                    <a href="/babok/public/?route=knowledge_areas_view&id=<?= $task['knowledge_area_id'] ?>">
                        <?= htmlspecialchars($task['knowledge_area_code'] ?? '') ?>
                    </a>
                </p>
                <p><strong>تعداد تکنیک‌های پیشنهادی:</strong> 
                    <span class="badge badge-primary"><?= count($recommendedTechniques ?? []) ?></span>
                </p>
            </div>
        </div>
        <?php if (!empty($task['description'])): ?>
            <p><strong>توضیحات:</strong> <?= nl2br(htmlspecialchars($task['description'])) ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- تکنیک‌های پیشنهادی -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-tools"></i> تکنیک‌های پیشنهادی
        </div>
        <div>
            <span class="badge badge-success"><?= count($recommendedTechniques ?? []) ?> تکنیک</span>
        </div>
    </div>

    <?php if (empty($recommendedTechniques)): ?>
        <div class="text-muted text-center" style="padding: 40px 0;">
            <i class="fas fa-tools" style="font-size: 3rem; opacity: 0.3;"></i>
            <p style="margin-top: 10px;">هیچ تکنیکی برای این وظیفه پیشنهاد نشده است.</p>
            <p class="text-muted">برای این وظیفه هنوز تکنیک‌های مرتبط تعریف نشده‌اند.</p>
        </div>
    <?php else: ?>
        <div class="techniques-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; padding: 15px;">
            <?php foreach ($recommendedTechniques as $tech): ?>
                <div class="technique-card" style="background: white; border: 1px solid #f0f0f0; border-radius: 12px; padding: 16px; transition: 0.3s; box-shadow: var(--shadow); border-top: 4px solid <?= match($tech['category']) {
                    'collaborative' => '#17a2b8',
                    'strategic' => '#e74c3c',
                    'modeling' => '#3498db',
                    'management' => '#95a5a6',
                    'research' => '#27ae60',
                    'experimental' => '#f39c12',
                    default => '#3498db'
                } ?>;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="font-weight: 700; font-size: 1.1rem;"><?= htmlspecialchars($tech['name']) ?></div>
                        <span class="badge <?= match($tech['category']) {
                            'collaborative' => 'badge-info',
                            'strategic' => 'badge-danger',
                            'modeling' => 'badge-primary',
                            'management' => 'badge-secondary',
                            'research' => 'badge-success',
                            'experimental' => 'badge-warning',
                            default => 'badge-secondary'
                        } ?>">
                            <?= ucfirst($tech['category']) ?>
                        </span>
                    </div>
                    
                    <?php if (!empty($tech['purpose'])): ?>
                        <div style="font-size: 0.85rem; color: #555; margin: 8px 0;">
                            <strong>هدف:</strong> <?= htmlspecialchars(substr($tech['purpose'], 0, 100)) . (strlen($tech['purpose'] ?? '') > 100 ? '...' : '') ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($tech['relevance_score'])): ?>
                        <div style="margin: 8px 0;">
                            <div style="display: flex; justify-content: space-between; font-size: 0.8rem;">
                                <span>امتیاز relevance:</span>
                                <span><strong><?= $tech['relevance_score'] ?? 0 ?>%</strong></span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-<?= ($tech['relevance_score'] ?? 0) > 70 ? 'success' : (($tech['relevance_score'] ?? 0) > 40 ? 'warning' : 'secondary') ?>" 
                                     style="width: <?= $tech['relevance_score'] ?? 0 ?>%;">
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($tech['reason'])): ?>
                        <div style="font-size: 0.8rem; color: var(--secondary-color); margin: 5px 0;">
                            <i class="fas fa-info-circle"></i> <?= htmlspecialchars($tech['reason']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div style="margin-top: 10px;">
                        <a href="/babok/public/?route=techniques_view&id=<?= $tech['id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i> مشاهده جزئیات
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- تکنیک‌های موجود (اختیاری) -->
<?php if (!empty($allTechniques)): ?>
<div class="card mt-3">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-list"></i> همه تکنیک‌ها
        </div>
        <div>
            <span class="badge badge-secondary"><?= count($allTechniques) ?> تکنیک</span>
        </div>
    </div>
    <div style="max-height: 300px; overflow-y: auto; padding: 10px;">
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($allTechniques as $tech): ?>
                <span class="badge badge-secondary" style="font-size: 0.8rem; padding: 5px 12px;">
                    <?= htmlspecialchars($tech['name']) ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>