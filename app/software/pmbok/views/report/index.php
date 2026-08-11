<?php
$pageTitle = 'گزارش‌ها - PMBOK';
$activePage = 'report';
?>

<div class="page-header">
    <div>
        <h2><i class="fas fa-chart-bar"></i> داشبورد گزارش‌ها</h2>
        <p class="text-muted">نمای کلی از وضعیت پروژه‌ها و ریسک‌ها</p>
    </div>
</div>

<!-- آمار کلی -->
<div class="stats-grid">
    <div class="card stat-card">
        <div class="stat-icon" style="color: var(--soft-primary);"><i class="fas fa-project-diagram"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['total_projects'] ?? 0 ?></div>
            <div class="stat-label">پروژه</div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="color: var(--soft-success);"><i class="fas fa-tasks"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['total_tasks'] ?? 0 ?></div>
            <div class="stat-label">فرآیند</div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="color: var(--soft-warning);"><i class="fas fa-tools"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['total_techniques'] ?? 0 ?></div>
            <div class="stat-label">تکنیک</div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="color: var(--soft-danger);"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['total_risks'] ?? 0 ?></div>
            <div class="stat-label">ریسک</div>
        </div>
    </div>
</div>

<div class="main-grid">
    <!-- پروژه‌ها بر اساس فاز -->
    <div class="card">
        <h3 class="card-title"><i class="fas fa-layer-group"></i> پروژه‌ها بر اساس فاز</h3>
        <?php if (empty($stats['projects_by_phase'])): ?>
            <p class="text-muted">داده‌ای وجود ندارد.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr><th>فاز</th><th>تعداد</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['projects_by_phase'] as $row): ?>
                    <tr>
                        <td>
                            <span class="badge badge-<?= pmbok_getPhaseColor($row['phase']) ?>">
                                <?= pmbok_getPhaseLabel($row['phase']) ?>
                            </span>
                        </td>
                        <td><strong><?= $row['count'] ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- پروژه‌ها بر اساس متدولوژی -->
    <div class="card">
        <h3 class="card-title"><i class="fas fa-diagram-project"></i> پروژه‌ها بر اساس متدولوژی</h3>
        <?php if (empty($stats['projects_by_methodology'])): ?>
            <p class="text-muted">داده‌ای وجود ندارد.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr><th>متدولوژی</th><th>تعداد</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['projects_by_methodology'] as $row): ?>
                    <tr>
                        <td><?= pmbok_getMethodologyLabel($row['methodology']) ?></td>
                        <td><strong><?= $row['count'] ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- فرآیندها بر اساس حوزه دانشی -->
<div class="card" style="margin-top: 20px;">
    <h3 class="card-title"><i class="fas fa-sitemap"></i> فرآیندها بر اساس حوزه دانشی</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">
        <?php foreach ($stats['tasks_by_ka'] as $row): ?>
            <div class="card" style="padding: 15px; margin: 0;">
                <h4 style="margin: 0; font-size: 1rem; color: var(--soft-primary);">
                    <?= htmlspecialchars($row['name']) ?>
                </h4>
                <code style="font-size: 0.8rem; color: var(--gray);"><?= htmlspecialchars($row['code']) ?></code>
                <div style="margin-top: 10px;">
                    <span class="badge badge-primary"><?= $row['count'] ?> فرآیند</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ریسک‌های بحرانی -->
<div class="card" style="margin-top: 20px;">
    <h3 class="card-title"><i class="fas fa-fire"></i> ریسک‌های بحرانی</h3>
    <?php if (empty($stats['high_risks'])): ?>
        <p class="text-muted"><i class="fas fa-check-circle" style="color: #10B981;"></i> ریسک بحرانی وجود ندارد.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>عنوان</th>
                    <th>پروژه</th>
                    <th>احتمال</th>
                    <th>تاثیر</th>
                    <th>امتیاز</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats['high_risks'] as $r): ?>
                <tr>
                    <td>
                        <a href="?controller=risk&action=show&id=<?= $r['id'] ?>">
                            <?= htmlspecialchars($r['title']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($r['project_name']) ?></td>
                    <td><span class="badge"><?= pmbok_getProbabilityLabel($r['probability']) ?></span></td>
                    <td><span class="badge"><?= pmbok_getImpactLabel($r['impact']) ?></span></td>
                    <td>
                        <strong style="color: <?= $r['risk_score'] >= 20 ? '#DC2626' : '#F59E0B' ?>">
                            <?= $r['risk_score'] ?>
                        </strong>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>