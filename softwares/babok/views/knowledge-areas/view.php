<?php
// داده‌های ارسالی: $knowledgeArea, $tasks
$pageTitle = htmlspecialchars($knowledgeArea['name']) . ' - BABOK Analyzer';
$activePage = 'knowledge_areas';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>
        <i class="fas fa-folder-open" style="color: var(--secondary-color);"></i>
        <?= htmlspecialchars($knowledgeArea['code']) ?> - <?= htmlspecialchars($knowledgeArea['name']) ?>
    </h2>
    <a href="/babok/public/?route=knowledge_areas" class="btn btn-secondary">
        <i class="fas fa-arrow-right"></i> بازگشت
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <p><strong>توضیحات:</strong> <?= nl2br(htmlspecialchars($knowledgeArea['description'] ?? 'بدون توضیحات')) ?></p>
        <p><strong>تعداد وظایف:</strong> <?= count($tasks) ?></p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-tasks"></i> وظایف این حوزه
        </div>
        <span class="badge badge-primary"><?= count($tasks) ?> وظیفه</span>
    </div>

    <?php if (empty($tasks)): ?>
        <div class="text-muted text-center" style="padding: 30px 0;">
            <i class="fas fa-inbox" style="font-size: 2rem; opacity: 0.3;"></i>
            <p>هیچ وظیفه‌ای برای این حوزه تعریف نشده است.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>کد</th>
                        <th>نام وظیفه</th>
                        <th>تکنیک‌های مرتبط</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($task['code']) ?></strong></td>
                        <td><?= htmlspecialchars($task['name']) ?></td>
                        <td>
                            <?php if (!empty($task['techniques'])): ?>
                                <?php 
                                $techniques = explode(', ', $task['techniques']);
                                foreach ($techniques as $tech): 
                                ?>
                                    <span class="badge badge-info"><?= htmlspecialchars($tech) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted">هیچ تکنیکی</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/babok/public/?route=tasks_view&id=<?= $task['id'] ?>" class="btn btn-sm btn-primary">
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