<?php
$pageTitle = htmlspecialchars($task['name']) . ' - BABOK Analyzer';
$activePage = 'tasks';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>
        <i class="fas fa-tasks" style="color: var(--secondary-color);"></i>
        <?= htmlspecialchars($task['code']) ?> - <?= htmlspecialchars($task['name']) ?>
    </h2>
    <a href="/babok/public/?route=tasks" class="btn btn-secondary">
        <i class="fas fa-arrow-right"></i> بازگشت
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td width="150"><strong>حوزه دانشی:</strong></td>
                        <td>
                            <?php if (!empty($knowledgeArea)): ?>
                                <a href="/babok/public/?route=knowledge_areas_view&id=<?= $knowledgeArea['id'] ?>" 
                                   class="badge badge-primary" style="text-decoration: none; font-size: 0.9rem;">
                                    <?= htmlspecialchars($knowledgeArea['code']) ?> - <?= htmlspecialchars($knowledgeArea['name']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">نامشخص</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if (!empty($task['description'])): ?>
                    <tr>
                        <td><strong>توضیحات:</strong></td>
                        <td><?= nl2br(htmlspecialchars($task['description'])) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($task['inputs'])): ?>
                    <tr>
                        <td><strong>ورودی‌ها:</strong></td>
                        <td><?= nl2br(htmlspecialchars($task['inputs'])) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($task['outputs'])): ?>
                    <tr>
                        <td><strong>خروجی‌ها:</strong></td>
                        <td><?= nl2br(htmlspecialchars($task['outputs'])) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($task['stakeholders'])): ?>
                    <tr>
                        <td><strong>ذی‌نفعان:</strong></td>
                        <td><?= nl2br(htmlspecialchars($task['stakeholders'])) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-3 text-center">
            <div class="card-body">
                <h5><i class="fas fa-tools"></i> تکنیک‌های مرتبط</h5>
                <div style="font-size: 2.5rem; font-weight: 700; color: var(--secondary-color);">
                    <?= count($techniques ?? []) ?>
                </div>
                <p class="text-muted">تکنیک برای این وظیفه</p>
                <a href="/babok/public/?route=recommendations_task&id=<?= $task['id'] ?>" class="btn btn-sm btn-success w-100 mt-2">
                    <i class="fas fa-lightbulb"></i> دریافت پیشنهادات
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-tools"></i> تکنیک‌های مرتبط با این وظیفه
        </div>
        <span class="badge badge-primary"><?= count($techniques ?? []) ?> تکنیک</span>
    </div>

    <?php if (empty($techniques)): ?>
        <div class="text-muted text-center" style="padding: 30px 0;">
            <i class="fas fa-tools" style="font-size: 2rem; opacity: 0.3;"></i>
            <p>هیچ تکنیکی برای این وظیفه تعریف نشده است.</p>
            <a href="/babok/public/?route=recommendations_task&id=<?= $task['id'] ?>" class="btn btn-sm btn-success">
                <i class="fas fa-lightbulb"></i> دریافت پیشنهادات
            </a>
        </div>
    <?php else: ?>
        <div class="techniques-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; padding: 10px;">
            <?php foreach ($techniques as $tech): ?>
                <div class="technique-card" style="background: white; border: 1px solid #f0f0f0; border-radius: 12px; padding: 12px; transition: 0.3s; box-shadow: var(--shadow);">
                    <div style="font-weight: 700;"><?= htmlspecialchars($tech['name']) ?></div>
                    <div style="font-size: 0.75rem; color: #7f8c8d; margin: 3px 0;">
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
                    <div style="font-size: 0.8rem; color: #555; margin: 5px 0;">
                        <?= htmlspecialchars(substr($tech['purpose'] ?? '', 0, 60)) . '...' ?>
                    </div>
                    <a href="/babok/public/?route=techniques_view&id=<?= $tech['id'] ?>" class="btn btn-sm btn-primary mt-2">
                        <i class="fas fa-eye"></i> مشاهده
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>