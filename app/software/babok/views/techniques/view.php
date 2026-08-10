<?php
/**
 * ویو مشاهده جزئیات تکنیک
 * مسیر: app/software/babok/views/techniques/view.php
 */
$pageTitle = $technique['name'] . ' - BABOK Analyzer';
$activePage = 'techniques';

$categoryLabels = [
    'collaborative' => 'همکاری',
    'research' => 'تحقیقاتی',
    'experimental' => 'آزمایشی',
    'management' => 'مدیریتی',
    'strategic' => 'استراتژیک',
    'modeling' => 'مدل‌سازی'
];
?>

<!-- هدر تکنیک -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-tools"></i> <?= htmlspecialchars($technique['name']) ?>
        </div>
        <div class="card-tools">
            <span class="badge category-<?= $technique['category'] ?>" style="font-size: 0.9rem; padding: 6px 15px;">
                <?= $categoryLabels[$technique['category']] ?? $technique['category'] ?>
            </span>
            <a href="?route=techniques" class="btn btn-secondary">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>
    
    <!-- هدف -->
    <?php if (!empty($technique['purpose'])): ?>
        <div style="margin-bottom: 20px;">
            <h5 style="margin-bottom: 8px;"><i class="fas fa-bullseye"></i> هدف</h5>
            <p style="line-height: 1.8;"><?= nl2br(htmlspecialchars($technique['purpose'])) ?></p>
        </div>
    <?php endif; ?>
    
    <!-- توضیحات -->
    <?php if (!empty($technique['description'])): ?>
        <div style="margin-bottom: 20px;">
            <h5 style="margin-bottom: 8px;"><i class="fas fa-align-right"></i> توضیحات</h5>
            <p style="line-height: 1.8;"><?= nl2br(htmlspecialchars($technique['description'])) ?></p>
        </div>
    <?php endif; ?>
    
    <!-- مزایا -->
    <?php if (!empty($technique['advantages'])): ?>
        <div style="margin-bottom: 20px;">
            <h5 style="margin-bottom: 8px; color: var(--soft-success);"><i class="fas fa-check-circle"></i> مزایا</h5>
            <p style="line-height: 1.8;"><?= nl2br(htmlspecialchars($technique['advantages'])) ?></p>
        </div>
    <?php endif; ?>
    
    <!-- معایب -->
    <?php if (!empty($technique['disadvantages'])): ?>
        <div style="margin-bottom: 20px;">
            <h5 style="margin-bottom: 8px; color: var(--soft-danger);"><i class="fas fa-times-circle"></i> معایب</h5>
            <p style="line-height: 1.8;"><?= nl2br(htmlspecialchars($technique['disadvantages'])) ?></p>
        </div>
    <?php endif; ?>
    
    <!-- ملاحظات استفاده -->
    <?php if (!empty($technique['usage_considerations'])): ?>
        <div>
            <h5 style="margin-bottom: 8px; color: var(--soft-warning);"><i class="fas fa-exclamation-triangle"></i> ملاحظات استفاده</h5>
            <p style="line-height: 1.8;"><?= nl2br(htmlspecialchars($technique['usage_considerations'])) ?></p>
        </div>
    <?php endif; ?>
</div>

<!-- وظایف مرتبط با این تکنیک -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-tasks"></i> وظایف مرتبط با این تکنیک (<?= count($tasks) ?>)
        </div>
    </div>
    
    <?php if (empty($tasks)): ?>
        <p class="text-muted">هیچ وظیفه‌ای با این تکنیک مرتبط نیست.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 80px;">کد</th>
                        <th>نام وظیفه</th>
                        <th style="width: 100px;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><span class="badge badge-secondary"><?= htmlspecialchars($task['code']) ?></span></td>
                        <td><?= htmlspecialchars($task['name']) ?></td>
                        <td>
                            <a href="?route=tasks_view&id=<?= $task['id'] ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>