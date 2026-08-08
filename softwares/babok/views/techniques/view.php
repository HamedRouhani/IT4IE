<?php
$pageTitle = htmlspecialchars($technique['name']) . ' - BABOK Analyzer';
$activePage = 'techniques';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>
        <i class="fas fa-tools" style="color: var(--secondary-color);"></i>
        <?= htmlspecialchars($technique['name']) ?>
    </h2>
    <a href="/babok/public/?route=techniques" class="btn btn-secondary">
        <i class="fas fa-arrow-right"></i> بازگشت
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td width="150"><strong>نام:</strong></td>
                        <td><?= htmlspecialchars($technique['name']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>دسته‌بندی:</strong></td>
                        <td>
                            <span class="badge <?= match($technique['category']) {
                                'collaborative' => 'badge-info',
                                'strategic' => 'badge-danger',
                                'modeling' => 'badge-primary',
                                'management' => 'badge-secondary',
                                'research' => 'badge-success',
                                'experimental' => 'badge-warning',
                                default => 'badge-secondary'
                            } ?>">
                                <?= ucfirst($technique['category']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php if (!empty($technique['purpose'])): ?>
                    <tr>
                        <td><strong>هدف:</strong></td>
                        <td><?= nl2br(htmlspecialchars($technique['purpose'])) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($technique['description'])): ?>
                    <tr>
                        <td><strong>توضیحات:</strong></td>
                        <td><?= nl2br(htmlspecialchars($technique['description'])) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($technique['advantages'])): ?>
                    <tr>
                        <td><strong>مزایا:</strong></td>
                        <td><?= nl2br(htmlspecialchars($technique['advantages'])) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($technique['disadvantages'])): ?>
                    <tr>
                        <td><strong>معایب:</strong></td>
                        <td><?= nl2br(htmlspecialchars($technique['disadvantages'])) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-3 text-center">
            <div class="card-body">
                <h5><i class="fas fa-tasks"></i> وظایف مرتبط</h5>
                <div style="font-size: 2.5rem; font-weight: 700; color: var(--secondary-color);">
                    <?= count($tasks ?? []) ?>
                </div>
                <p class="text-muted">وظیفه مرتبط با این تکنیک</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-tasks"></i> وظایف مرتبط با این تکنیک
        </div>
        <span class="badge badge-primary"><?= count($tasks ?? []) ?> وظیفه</span>
    </div>

    <?php if (empty($tasks)): ?>
        <div class="text-muted text-center" style="padding: 30px 0;">
            <i class="fas fa-inbox" style="font-size: 2rem; opacity: 0.3;"></i>
            <p>هیچ وظیفه‌ای با این تکنیک مرتبط نیست.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>کد</th>
                        <th>نام وظیفه</th>
                        <th>حوزه دانشی</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($task['code']) ?></strong></td>
                        <td><?= htmlspecialchars($task['name']) ?></td>
                        <td>
                            <?php if (!empty($task['knowledge_area_id'])): ?>
                                <a href="/babok/public/?route=knowledge_areas_view&id=<?= $task['knowledge_area_id'] ?>" 
                                   class="badge badge-primary" style="text-decoration: none;">
                                    <?= htmlspecialchars($task['knowledge_area_code'] ?? 'KA' . $task['knowledge_area_id']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">نامشخص</span>
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