<?php
$pageTitle = ($task['name'] ?? 'فرآیند') . ' - PMBOK';
$activePage = 'task';
?>

<div class="page-header">
    <nav class="breadcrumb">
        <a href="?controller=task">فرآیندها</a> /
        <span><?= htmlspecialchars($task['code']) ?></span>
    </nav>
    <h2><i class="fas fa-tasks"></i> <?= htmlspecialchars($task['name']) ?></h2>
    <p class="text-muted">حوزه دانشی: 
        <a href="?controller=knowledgeArea&action=show&id=<?= $task['ka_id'] ?>">
            <?= htmlspecialchars($task['ka_name']) ?>
        </a>
    </p>
</div>

<div class="card">
    <h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات فرآیند</h3>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">کد:</span>
            <code><?= htmlspecialchars($task['code']) ?></code>
        </div>
        <div class="info-item">
            <span class="info-label">حوزه دانشی:</span>
            <span><?= htmlspecialchars($task['ka_name']) ?></span>
        </div>
    </div>
    
    <?php if (!empty($task['description'])): ?>
    <div style="margin-top: 15px;">
        <strong>توضیحات:</strong>
        <p style="margin-top: 5px; line-height: 1.8;"><?= nl2br(htmlspecialchars($task['description'])) ?></p>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($task['inputs'])): ?>
    <div style="margin-top: 15px;">
        <strong>ورودی‌ها:</strong>
        <p style="margin-top: 5px;"><?= nl2br(htmlspecialchars($task['inputs'])) ?></p>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($task['outputs'])): ?>
    <div style="margin-top: 15px;">
        <strong>خروجی‌ها:</strong>
        <p style="margin-top: 5px;"><?= nl2br(htmlspecialchars($task['outputs'])) ?></p>
    </div>
    <?php endif; ?>
</div>

<!-- تکنیک‌ها -->
<div class="card">
    <h3 class="card-title"><i class="fas fa-tools"></i> تکنیک‌ها و ابزارها (<?= count($techniques ?? []) ?>)</h3>
    <?php if (empty($techniques)): ?>
        <p class="text-muted">تکنیکی برای این فرآیند ثبت نشده است.</p>
    <?php else: ?>
        <div class="techniques-grid">
            <?php foreach ($techniques as $tech): ?>
                <a href="?controller=technique&action=show&id=<?= $tech['id'] ?>" class="technique-card">
                    <div class="technique-icon">
                        <i class="fas fa-microchip"></i>
                    </div>
                    <div class="technique-info">
                        <h4><?= htmlspecialchars($tech['name']) ?></h4>
                        <?php if (!empty($tech['category'])): ?>
                            <span class="badge badge-secondary"><?= htmlspecialchars($tech['category']) ?></span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>