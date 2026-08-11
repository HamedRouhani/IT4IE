<?php
$pageTitle = ($ka['name'] ?? 'حوزه دانشی') . ' - PMBOK';
$activePage = 'knowledgeArea';
?>

<div class="page-header">
    <nav class="breadcrumb">
        <a href="?controller=knowledgeArea">حوزه‌های دانشی</a> /
        <span><?= htmlspecialchars($ka['name']) ?></span>
    </nav>
    <h2><i class="fas fa-sitemap"></i> <?= htmlspecialchars($ka['name']) ?></h2>
</div>

<div class="card">
    <h3 class="card-title"><i class="fas fa-info-circle"></i> توضیحات</h3>
    <p style="line-height: 1.8;"><?= nl2br(htmlspecialchars($ka['description'] ?? 'توضیحاتی ثبت نشده است.')) ?></p>
</div>

<!-- فرآیندهای این حوزه -->
<div class="card">
    <h3 class="card-title"><i class="fas fa-tasks"></i> فرآیندها (<?= count($tasks ?? []) ?>)</h3>
    <?php if (empty($tasks)): ?>
        <p class="text-muted">فرآیندی ثبت نشده است.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>کد</th>
                    <th>نام فرآیند</th>
                    <th>تکنیک‌ها</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $t): ?>
                <tr>
                    <td><code><?= htmlspecialchars($t['code']) ?></code></td>
                    <td><?= htmlspecialchars($t['name']) ?></td>
                    <td><span class="badge"><?= $t['technique_count'] ?? 0 ?></span></td>
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

<!-- تکنیک‌های این حوزه -->
<div class="card">
    <h3 class="card-title"><i class="fas fa-tools"></i> تکنیک‌ها (<?= count($techniques ?? []) ?>)</h3>
    <?php if (empty($techniques)): ?>
        <p class="text-muted">تکنیکی ثبت نشده است.</p>
    <?php else: ?>
        <div class="techniques-grid">
            <?php foreach ($techniques as $tech): ?>
                <a href="?controller=technique&action=show&id=<?= $tech['id'] ?>" class="technique-card">
                    <div class="technique-icon"><i class="fas fa-microchip"></i></div>
                    <div class="technique-info">
                        <h4><?= htmlspecialchars($tech['name']) ?></h4>
                        <span class="badge"><?= $tech['task_count'] ?? 0 ?> فرآیند</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- پروژه‌های مرتبط -->
<div class="card">
    <h3 class="card-title"><i class="fas fa-project-diagram"></i> پروژه‌های مرتبط (<?= count($projects ?? []) ?>)</h3>
    <?php if (empty($projects)): ?>
        <p class="text-muted">پروژه‌ای از فرآیندهای این حوزه استفاده نکرده است.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>نام پروژه</th>
                    <th>فاز</th>
                    <th>تعداد فرآیندهای مرتبط</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><span class="badge badge-<?= pmbok_getPhaseColor($p['phase']) ?>"><?= pmbok_getPhaseLabel($p['phase']) ?></span></td>
                    <td><span class="badge"><?= $p['task_count'] ?? 0 ?></span></td>
                    <td>
                        <a href="?controller=project&action=show&id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>