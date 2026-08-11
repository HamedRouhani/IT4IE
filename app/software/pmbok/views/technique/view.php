<?php
$pageTitle = ($technique['name'] ?? 'تکنیک') . ' - PMBOK';
$activePage = 'technique';
?>

<div class="page-header">
    <nav class="breadcrumb">
        <a href="?controller=technique">تکنیک‌ها</a> /
        <span><?= htmlspecialchars($technique['name']) ?></span>
    </nav>
    <h2><i class="fas fa-microchip"></i> <?= htmlspecialchars($technique['name']) ?></h2>
    <?php if (!empty($technique['category'])): ?>
        <span class="badge badge-secondary"><?= htmlspecialchars($technique['category']) ?></span>
    <?php endif; ?>
</div>

<div class="card">
    <h3 class="card-title"><i class="fas fa-info-circle"></i> توضیحات</h3>
    <p style="line-height: 1.8;"><?= nl2br(htmlspecialchars($technique['description'] ?? 'توضیحاتی ثبت نشده است.')) ?></p>
    
    <?php if (!empty($technique['purpose'])): ?>
    <div style="margin-top: 15px;">
        <strong>هدف:</strong>
        <p style="margin-top: 5px;"><?= nl2br(htmlspecialchars($technique['purpose'])) ?></p>
    </div>
    <?php endif; ?>
</div>

<!-- فرآیندهای مرتبط -->
<div class="card">
    <h3 class="card-title"><i class="fas fa-tasks"></i> فرآیندهای مرتبط (<?= count($tasks ?? []) ?>)</h3>
    <?php if (empty($tasks)): ?>
        <p class="text-muted">فرآیندی از این تکنیک استفاده نمی‌کند.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>کد</th>
                    <th>نام فرآیند</th>
                    <th>حوزه دانشی</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $t): ?>
                <tr>
                    <td><code><?= htmlspecialchars($t['code']) ?></code></td>
                    <td><?= htmlspecialchars($t['name']) ?></td>
                    <td><?= htmlspecialchars($t['ka_name']) ?></td>
                    <td>
                        <a href="?controller=task&action=show&id=<?= $t['id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- حوزه‌های دانشی مرتبط -->
<div class="card">
    <h3 class="card-title"><i class="fas fa-sitemap"></i> حوزه‌های دانشی مرتبط (<?= count($knowledgeAreas ?? []) ?>)</h3>
    <?php if (empty($knowledgeAreas)): ?>
        <p class="text-muted">حوزه دانشی مرتبط یافت نشد.</p>
    <?php else: ?>
        <div class="ka-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">
            <?php foreach ($knowledgeAreas as $kaItem): ?>
                <a href="?controller=knowledgeArea&action=show&id=<?= $kaItem['id'] ?>" class="card" style="text-decoration: none; color: inherit;">
                    <h4 style="color: var(--soft-primary);"><?= htmlspecialchars($kaItem['name']) ?></h4>
                    <span class="badge"><?= $kaItem['task_count'] ?? 0 ?> فرآیند</span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>