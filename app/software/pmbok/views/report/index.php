<?php
$pageTitle = 'گزارش‌ها و خروجی‌ها - PMBOK';
$activePage = 'report';
?>

<div class="page-header">
    <div>
        <h2><i class="fas fa-chart-bar"></i> داشبورد گزارش‌ها و خروجی‌ها</h2>
        <p class="text-muted">نمای کلی از وضعیت پروژه‌ها، فرآیندها و ریسک‌ها | دانلود خروجی MS Project و Primavera P6</p>
    </div>
</div>

<!-- آمار کلی -->
<div class="stats-grid">
    <div class="card stat-card">
        <div class="stat-icon" style="color: var(--soft-primary);"><i class="fas fa-project-diagram"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['total_projects'] ?? 0 ?></div>
            <div class="stat-label">پروژه فعال</div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="color: var(--soft-success);"><i class="fas fa-tasks"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['total_tasks'] ?? 0 ?></div>
            <div class="stat-label">فرآیند تعریف‌شده</div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="color: var(--soft-warning);"><i class="fas fa-tools"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['total_techniques'] ?? 0 ?></div>
            <div class="stat-label">تکنیک موجود</div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="color: var(--soft-danger);"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['total_risks'] ?? 0 ?></div>
            <div class="stat-label">ریسک شناسایی‌شده</div>
        </div>
    </div>
</div>

<!-- لیست پروژه‌ها با امکان دانلود خروجی -->
<div class="card" style="margin-top: 20px;">
    <h3 class="card-title"><i class="fas fa-file-export"></i> پروژه‌ها و خروجی‌ها</h3>
    <?php if (empty($projects)): ?>
        <p class="text-muted" style="text-align: center; padding: 30px;">
            <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 10px;"></i>
            هنوز پروژه‌ای ایجاد نشده است.
        </p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>نام پروژه</th>
                        <th>فاز</th>
                        <th>متدولوژی</th>
                        <th>فرآیندها</th>
                        <th>ریسک‌ها</th>
                        <th style="width: 300px;">عملیات خروجی</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $proj): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($proj['name']) ?></strong>
                            <?php if (!empty($proj['description'])): ?>
                                <br><small class="text-muted"><?= htmlspecialchars(mb_substr($proj['description'], 0, 50)) ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-<?= pmbok_getPhaseColor($proj['phase']) ?>">
                                <?= pmbok_getPhaseLabel($proj['phase']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-info">
                                <?= pmbok_getMethodologyLabel($proj['methodology']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-secondary"><?= $proj['task_count'] ?? 0 ?></span>
                        </td>
                        <td>
                            <span class="badge badge-<?= ($proj['risk_count'] ?? 0) > 5 ? 'danger' : 'warning' ?>">
                                <?= $proj['risk_count'] ?? 0 ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                <a href="?controller=report&action=projectReport&id=<?= $proj['id'] ?>" 
                                   class="btn btn-sm btn-primary" 
                                   title="گزارش HTML قابل چاپ"
                                   target="_blank">
                                    <i class="fas fa-file-alt"></i> گزارش
                                </a>
                                <a href="?controller=report&action=exportMsProject&id=<?= $proj['id'] ?>" 
                                   class="btn btn-sm btn-success" 
                                   title="دانلود فایل XML برای MS Project">
                                    <i class="fas fa-file-code"></i> MSP
                                </a>
                                <a href="?controller=report&action=exportPrimavera&id=<?= $proj['id'] ?>" 
                                   class="btn btn-sm btn-warning" 
                                   title="دانلود فایل CSV برای Primavera P6">
                                    <i class="fas fa-file-csv"></i> P6
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- تحلیل‌های آماری -->
<div class="main-grid" style="margin-top: 20px;">
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
    <?php if (empty($stats['tasks_by_ka'])): ?>
        <p class="text-muted">داده‌ای وجود ندارد.</p>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">
            <?php foreach ($stats['tasks_by_ka'] as $row): ?>
                <div class="card" style="padding: 15px; margin: 0; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                    <h4 style="margin: 0 0 5px 0; font-size: 1rem; color: var(--soft-primary);">
                        <?= htmlspecialchars($row['name']) ?>
                    </h4>
                    <code style="font-size: 0.8rem; color: var(--gray);"><?= htmlspecialchars($row['code']) ?></code>
                    <div style="margin-top: 10px;">
                        <span class="badge badge-primary"><?= $row['count'] ?> فرآیند</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ریسک‌های بحرانی -->
<div class="card" style="margin-top: 20px;">
    <h3 class="card-title"><i class="fas fa-fire"></i> ریسک‌های بحرانی (امتیاز ≥ 20)</h3>
    <?php if (empty($stats['high_risks'])): ?>
        <p class="text-muted" style="text-align: center; padding: 20px;">
            <i class="fas fa-check-circle" style="color: #10B981; font-size: 2rem; display: block; margin-bottom: 10px;"></i>
            ریسک بحرانی وجود ندارد.
        </p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>عنوان ریسک</th>
                        <th>پروژه</th>
                        <th>احتمال</th>
                        <th>تأثیر</th>
                        <th>امتیاز</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['high_risks'] as $r): ?>
                    <tr style="background: <?= $r['risk_score'] >= 20 ? '#fef2f2' : '#fffbeb' ?>;">
                        <td>
                            <a href="?controller=risk&action=show&id=<?= $r['id'] ?>" style="color: var(--soft-primary);">
                                <?= htmlspecialchars($r['title']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($r['project_name']) ?></td>
                        <td><span class="badge"><?= pmbok_getProbabilityLabel($r['probability']) ?></span></td>
                        <td><span class="badge"><?= pmbok_getImpactLabel($r['impact']) ?></span></td>
                        <td>
                            <strong style="color: <?= $r['risk_score'] >= 20 ? '#DC2626' : '#F59E0B' ?>; font-size: 1.1rem;">
                                <?= $r['risk_score'] ?>
                            </strong>
                        </td>
                        <td>
                            <a href="?controller=risk&action=show&id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> مشاهده
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- راهنمای استفاده از خروجی‌ها -->
<div class="card" style="margin-top: 20px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-right: 4px solid #0284c7;">
    <h3 class="card-title" style="color: #0369a1;">
        <i class="fas fa-info-circle"></i> راهنمای استفاده از خروجی‌ها
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 15px;">
        <div>
            <h4 style="color: #0369a1; margin-bottom: 10px;">
                <i class="fas fa-file-alt" style="color: #3b82f6;"></i> گزارش HTML
            </h4>
            <p style="font-size: 0.9rem; color: #475569; margin: 0;">
                گزارش کامل پروژه با قابلیت چاپ و ذخیره به PDF. شامل تمام فرآیندها، ریسک‌ها و آمار پروژه.
            </p>
        </div>
        <div>
            <h4 style="color: #0369a1; margin-bottom: 10px;">
                <i class="fas fa-file-code" style="color: #10b981;"></i> MS Project (XML)
            </h4>
            <p style="font-size: 0.9rem; color: #475569; margin: 0;">
                فایل XML سازگار با Microsoft Project. قابل ایمپورت مستقیم در MSP 2016 و بالاتر.
            </p>
        </div>
        <div>
            <h4 style="color: #0369a1; margin-bottom: 10px;">
                <i class="fas fa-file-csv" style="color: #f59e0b;"></i> Primavera P6 (CSV)
            </h4>
            <p style="font-size: 0.9rem; color: #475569; margin: 0;">
                فایل CSV با ساختار استاندارد P6. قابل ایمپورت در Oracle Primavera P6 با UTF-8.
            </p>
        </div>
    </div>
</div>