<?php
// فقط محتوای اصلی - لایه‌بندی در layout/main.php تعریف شده است
$pageTitle = 'داشبورد - BABOK Analyzer';
$activePage = 'home';
?>

<!-- کارت‌های آمار -->
<div class="stats-grid">
    <div class="card" style="border-right: 5px solid var(--secondary-color);">
        <div style="font-size: 2rem; color: var(--secondary-color); opacity: 0.7;">
            <i class="fas fa-sitemap"></i>
        </div>
        <div style="font-size: 2rem; font-weight: 700; margin: 5px 0;"><?= count($knowledgeAreas ?? []) ?></div>
        <div style="color: #7f8c8d; font-size: 0.9rem;">حوزه‌های دانشی</div>
    </div>
    <div class="card" style="border-right: 5px solid var(--success-color);">
        <div style="font-size: 2rem; color: var(--success-color); opacity: 0.7;">
            <i class="fas fa-tasks"></i>
        </div>
        <div style="font-size: 2rem; font-weight: 700; margin: 5px 0;"><?= $totalTasks ?? 0 ?></div>
        <div style="color: #7f8c8d; font-size: 0.9rem;">وظایف BABOK</div>
    </div>
    <div class="card" style="border-right: 5px solid var(--warning-color);">
        <div style="font-size: 2rem; color: var(--warning-color); opacity: 0.7;">
            <i class="fas fa-microchip"></i>
        </div>
        <div style="font-size: 2rem; font-weight: 700; margin: 5px 0;"><?= $totalTechniques ?? 0 ?></div>
        <div style="color: #7f8c8d; font-size: 0.9rem;">تکنیک‌های استاندارد</div>
    </div>
    <div class="card" style="border-right: 5px solid var(--danger-color);">
        <div style="font-size: 2rem; color: var(--danger-color); opacity: 0.7;">
            <i class="fas fa-rocket"></i>
        </div>
        <div style="font-size: 2rem; font-weight: 700; margin: 5px 0;"><?= $activeProjectsCount ?? 0 ?></div>
        <div style="color: #7f8c8d; font-size: 0.9rem;">پروژه‌های فعال</div>
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
            <ul style="list-style: none; padding: 0;">
                <?php foreach ($knowledgeAreas as $area): ?>
                    <li style="padding: 10px 0; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center;">
                        <span>
                            <i class="fas fa-folder" style="color: var(--secondary-color); margin-left: 8px;"></i>
                            <?= htmlspecialchars($area['name']) ?>
                        </span>
                        <span class="badge badge-primary"><?= $area['task_count'] ?? 0 ?> وظیفه</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <div class="mt-3">
            <a href="/babok/public/?route=knowledge_areas" class="btn btn-sm btn-primary">
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
                <div style="display: flex; align-items: center; padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--light-bg); display: flex; align-items: center; justify-content: center; margin-left: 15px; color: var(--secondary-color);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 500;">
                            <?= htmlspecialchars($activity['task_name'] ?? 'وظیفه') ?>
                            <small class="text-muted">(<?= htmlspecialchars($activity['task_code'] ?? '') ?>)</small>
                        </div>
                        <div style="font-size: 0.8rem; color: #7f8c8d;">
                            <i class="far fa-calendar-alt"></i> 
                            <?= date('Y-m-d H:i', strtotime($activity['completed_at'] ?? 'now')) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <div class="mt-3">
            <a href="/babok/public/?route=tasks" class="btn btn-sm btn-secondary">
                <i class="fas fa-list"></i> مشاهده همه وظایف
            </a>
        </div>
    </div>
</div>

<!-- بخش پایین: لینک‌های سریع -->
<div class="card" style="margin-top: 20px;">
    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
        <a href="/babok/public/?route=projects_create" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> ایجاد پروژه جدید
        </a>
        <a href="/babok/public/?route=techniques" class="btn btn-secondary">
            <i class="fas fa-search"></i> جستجوی تکنیک‌ها
        </a>
        <a href="/babok/public/?route=recommendations_analyzer" class="btn btn-success">
            <i class="fas fa-robot"></i> تحلیل نیازمندی با هوش مصنوعی
        </a>
    </div>
</div>