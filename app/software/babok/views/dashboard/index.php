<?php
/**
 * ویو داشبورد اصلی BABOK
 * مسیر: app/software/babok/views/dashboard/index.php
 */
$pageTitle = 'داشبورد - BABOK Analyzer';
$activePage = 'home';
?>

<!-- هیرو داشبورد -->
<section class="bk-hero">
    <div>
        <span class="bk-eyebrow">BABOK v3 · Business Analysis</span>
        <h2>داشبورد تحلیل کسب‌وکار<?php if (!empty($_SESSION['user_name'])): ?>، <?= htmlspecialchars($_SESSION['user_name']) ?><?php endif; ?></h2>
        <p>نیازمندی‌ها را استخراج کنید، تکنیک مناسب BABOK را پیدا کنید و چرخهٔ حیات پروژه را در یک‌جا مدیریت کنید.</p>
    </div>
    <div class="bk-hero-actions">
        <a href="?route=projects_create" class="btn btn-light"><i class="fas fa-plus-circle"></i> پروژهٔ جدید</a>
        <a href="?route=requirement" class="btn btn-ghost"><i class="fas fa-robot"></i> استخراج نیازمندی</a>
    </div>
</section>

<!-- کارت‌های آمار -->
<div class="stats-grid">
    <div class="bk-stat bk-stat--ka">
        <span class="bk-stat-icon"><i class="fas fa-sitemap"></i></span>
        <div>
            <div class="bk-stat-value"><?= count($knowledgeAreas ?? []) ?></div>
            <div class="bk-stat-label">حوزه‌های دانشی</div>
        </div>
    </div>
    <div class="bk-stat bk-stat--task">
        <span class="bk-stat-icon"><i class="fas fa-tasks"></i></span>
        <div>
            <div class="bk-stat-value"><?= $totalTasks ?? 0 ?></div>
            <div class="bk-stat-label">وظایف BABOK</div>
        </div>
    </div>
    <div class="bk-stat bk-stat--tech">
        <span class="bk-stat-icon"><i class="fas fa-microchip"></i></span>
        <div>
            <div class="bk-stat-value"><?= $totalTechniques ?? 0 ?></div>
            <div class="bk-stat-label">تکنیک‌های استاندارد</div>
        </div>
    </div>
    <div class="bk-stat bk-stat--proj">
        <span class="bk-stat-icon"><i class="fas fa-rocket"></i></span>
        <div>
            <div class="bk-stat-value"><?= $activeProjectsCount ?? 0 ?></div>
            <div class="bk-stat-label">پروژه‌های فعال</div>
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