<?php
/**
 * ویو داشبورد اصلی BABOK
 * مسیر: app/software/babok/views/dashboard/index.php
 */
$pageTitle = 'داشبورد - BABOK Analyzer';
$activePage = 'home';
?>

<!-- ✅ کارت‌های آمار - با عناوین کامل -->
<div class="stats-grid">
    <div class="card stat-card-full" style="border-right: 5px solid var(--soft-secondary, #764ba2); text-align: center; padding: 25px 20px;">
        <div class="stat-icon" style="color: var(--soft-secondary, #764ba2); font-size: 1.9rem; margin-bottom: 12px;">
            <i class="fas fa-sitemap"></i>
        </div>
        <div class="stat-value" style="font-size: 2rem; font-weight: 800; color: #2D3748; line-height: 1.2;">
            <?= count($knowledgeAreas ?? []) ?>
        </div>
        <div class="stat-label" style="font-size: 0.95rem; color: #718096; margin-top: 8px; font-weight: 600;">
            حوزه‌های دانشی
        </div>
    </div>
    
    <div class="card stat-card-full" style="border-right: 5px solid var(--soft-success, #48bb78); text-align: center; padding: 25px 20px;">
        <div class="stat-icon" style="color: var(--soft-success, #48bb78); font-size: 1.9rem; margin-bottom: 12px;">
            <i class="fas fa-tasks"></i>
        </div>
        <div class="stat-value" style="font-size: 2rem; font-weight: 800; color: #2D3748; line-height: 1.2;">
            <?= $totalTasks ?? 0 ?>
        </div>
        <div class="stat-label" style="font-size: 0.95rem; color: #718096; margin-top: 8px; font-weight: 600;">
            وظایف BABOK
        </div>
    </div>
    
    <div class="card stat-card-full" style="border-right: 5px solid var(--soft-warning, #ed8936); text-align: center; padding: 25px 20px;">
        <div class="stat-icon" style="color: var(--soft-warning, #ed8936); font-size: 1.9rem; margin-bottom: 12px;">
            <i class="fas fa-microchip"></i>
        </div>
        <div class="stat-value" style="font-size: 2rem; font-weight: 800; color: #2D3748; line-height: 1.2;">
            <?= $totalTechniques ?? 0 ?>
        </div>
        <div class="stat-label" style="font-size: 0.95rem; color: #718096; margin-top: 8px; font-weight: 600;">
            تکنیک‌های استاندارد
        </div>
    </div>
    
    <div class="card stat-card-full" style="border-right: 5px solid var(--soft-danger, #f56565); text-align: center; padding: 25px 20px;">
        <div class="stat-icon" style="color: var(--soft-danger, #f56565); font-size: 1.9rem; margin-bottom: 12px;">
            <i class="fas fa-rocket"></i>
        </div>
        <div class="stat-value" style="font-size: 2rem; font-weight: 800; color: #2D3748; line-height: 1.2;">
            <?= $activeProjectsCount ?? 0 ?>
        </div>
        <div class="stat-label" style="font-size: 0.95rem; color: #718096; margin-top: 8px; font-weight: 600;">
            پروژه‌های فعال
        </div>
    </div>
</div>

<!-- بخش دو ستونه -->
<div class="main-grid">
    <!-- ستون راست: حوزه‌های دانشی -->
    <div class="card">
        <h3 class="card-title"><i class="fas fa-diagram-project"></i> حوزه‌های دانشی BABOK</h3>
        
        <?php if (empty($knowledgeAreas)): ?>
            <p class="text-muted">هیچ حوزه‌ای یافت نشد.</p>
        <?php else: ?>
            <ul class="knowledge-area-list">
                <?php foreach ($knowledgeAreas as $area): ?>
                    <li>
                        <span class="ka-name">
                            <i class="fas fa-folder"></i>
                            <?= htmlspecialchars($area['name']) ?>
                        </span>
                        <span class="badge badge-primary"><?= $area['task_count'] ?? 0 ?> وظیفه</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <div class="mt-3">
            <a href="?route=knowledge_areas" class="btn btn-sm btn-primary">
                <i class="fas fa-arrow-left"></i> مشاهده همه
            </a>
        </div>
    </div>
    
    <!-- ستون چپ: فعالیت‌های اخیر -->
    <div class="card">
        <h3 class="card-title"><i class="fas fa-clock"></i> آخرین فعالیت‌ها</h3>
        
        <?php if (empty($recentActivities)): ?>
            <p class="text-muted"><i class="fas fa-info-circle"></i> هیچ فعالیتی ثبت نشده است.</p>
        <?php else: ?>
            <?php foreach ($recentActivities as $activity): ?>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="activity-info">
                        <div class="activity-title">
                            <?= htmlspecialchars($activity['task_name'] ?? 'وظیفه') ?>
                            <small class="text-muted">(<?= htmlspecialchars($activity['task_code'] ?? '') ?>)</small>
                        </div>
                        <div class="activity-time">
                            <i class="far fa-calendar-alt"></i>
                            <?= date('Y-m-d H:i', strtotime($activity['completed_at'] ?? 'now')) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="mt-3">
            <a href="?route=tasks" class="btn btn-sm btn-secondary">
                <i class="fas fa-list"></i> مشاهده همه وظایف
            </a>
        </div>
    </div>
</div>

<!-- بخش پایین: پروژه‌های فعال -->
<?php if (!empty($activeProjects)): ?>
<div class="card" style="margin-top: 20px;">
    <h3 class="card-title"><i class="fas fa-rocket"></i> پروژه‌های فعال</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>نام پروژه</th>
                    <th>متدولوژی</th>
                    <th>فاز</th>
                    <th>پیشرفت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activeProjects as $project): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($project['name']) ?></strong></td>
                    <td>
                        <span class="badge methodology-<?= $project['methodology'] ?>">
                            <?= \App\Software\Babok\Helpers\Utils::methodologyLabel($project['methodology']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-secondary">
                            <?= \App\Software\Babok\Helpers\Utils::phaseLabel($project['phase']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="progress" style="width: 120px;">
                            <div class="progress-bar" style="width: <?= $project['progress'] ?? 0 ?>%"></div>
                        </div>
                        <small><?= $project['progress'] ?? 0 ?>%</small>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="?route=projects_view&id=<?= $project['id'] ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="?route=planning&id=<?= $project['id'] ?>" class="btn btn-sm btn-success">
                                <i class="fas fa-calendar-check"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- بخش پایین: لینک‌های سریع -->
<div class="card" style="margin-top: 20px;">
    <h3 class="card-title"><i class="fas fa-bolt"></i> دسترسی سریع</h3>
    <div class="d-flex gap-2 flex-wrap">
        <a href="?route=projects_create" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> ایجاد پروژه جدید
        </a>
        <a href="?route=techniques" class="btn btn-secondary">
            <i class="fas fa-search"></i> جستجوی تکنیک‌ها
        </a>
        <a href="?route=requirement" class="btn btn-success">
            <i class="fas fa-robot"></i> تحلیل نیازمندی با هوش مصنوعی
        </a>
    </div>
</div>