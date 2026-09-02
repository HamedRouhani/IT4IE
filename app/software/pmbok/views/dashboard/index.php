<?php
/**
 * داشبورد اصلی ماژول PMBOK Analyzer
 * مسیر: app/software/pmbok/views/dashboard/index.php
 * نسخه: ایمن‌سازی شده برای PHP 8+ (رفع خطای Undefined variable و null foreach)
 */

// ──────────────────────────────────────────────
// 🛡️ لایه ایمنی متغیرها (جلوگیری از خطاهای PHP 8)
// ──────────────────────────────────────────────
$pageTitle      = $pageTitle ?? 'داشبورد - PMBOK Analyzer';
$activePage     = $activePage ?? 'dashboard';
$isAuthenticated = $isAuthenticated ?? false;

// تبدیل به آرایه خالی در صورت null بودن (رفع خطای foreach)
$stats          = is_array($stats) ? $stats : [];
$knowledgeAreas = $knowledgeAreas ?? [];
$highRisks      = $highRisks ?? [];
$recentProjects = $recentProjects ?? [];
$activeProjects = $activeProjects ?? []; // ✅ رفع مستقیم خطای گزارش‌شده شما
?>

<!-- کارت‌های آمار -->
<div class="stats-grid">
    <div class="card stat-card" style="border-right: 5px solid var(--soft-primary, #667eea);">
        <div class="stat-icon"><i class="fas fa-sitemap"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['knowledge_areas'] ?? 0 ?></div>
            <div class="stat-label">حوزه‌های دانشی</div>
        </div>
    </div>
    
    <div class="card stat-card" style="border-right: 5px solid var(--soft-success, #48bb78);">
        <div class="stat-icon"><i class="fas fa-tasks"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['tasks'] ?? 0 ?></div>
            <div class="stat-label">فرآیندها (49)</div>
        </div>
    </div>
    
    <div class="card stat-card" style="border-right: 5px solid var(--soft-warning, #ed8936);">
        <div class="stat-icon"><i class="fas fa-microchip"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['techniques'] ?? 0 ?></div>
            <div class="stat-label">تکنیک‌ها</div>
        </div>
    </div>
    
    <div class="card stat-card" style="border-right: 5px solid var(--soft-info, #4299e1);">
        <div class="stat-icon"><i class="fas fa-rocket"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['projects'] ?? 0 ?></div>
            <div class="stat-label">پروژه‌ها</div>
        </div>
    </div>
    
    <div class="card stat-card" style="border-right: 5px solid var(--soft-danger, #f56565);">
        <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['risks'] ?? 0 ?></div>
            <div class="stat-label">ریسک‌ها</div>
        </div>
    </div>
    
    <div class="card stat-card" style="border-right: 5px solid var(--soft-secondary, #718096);">
        <div class="stat-icon"><i class="fas fa-box"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['deliverables'] ?? 0 ?></div>
            <div class="stat-label">تحویل‌دادنی‌ها</div>
        </div>
    </div>
</div>

<div class="main-grid">
    <!-- حوزه‌های دانشی -->
    <div class="card">
        <h3 class="card-title"><i class="fas fa-diagram-project"></i> حوزه‌های دانشی PMBOK</h3>
        <?php if (!empty($knowledgeAreas)): ?>
            <ul class="list-items">
                <?php foreach ($knowledgeAreas as $area): ?>
                    <li class="list-item">
                        <a href="?controller=knowledgeArea&action=show&id=<?= $area['id'] ?>" class="list-item-link">
                            <i class="fas fa-folder"></i>
                            <span><?= htmlspecialchars($area['name'] ?? 'نامشخص') ?></span>
                        </a>
                        <span class="badge badge-primary"><?= $area['task_count'] ?? 0 ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div style="margin-top: 15px;">
                <a href="?controller=knowledgeArea" class="btn btn-sm btn-primary">
                    <i class="fas fa-arrow-left"></i> مشاهده همه
                </a>
            </div>
        <?php else: ?>
            <p class="text-muted">هیچ حوزه‌ای یافت نشد.</p>
        <?php endif; ?>
    </div>
    
    <!-- ریسک‌های بحرانی -->
    <div class="card">
        <h3 class="card-title"><i class="fas fa-exclamation-triangle" style="color:#EF4444;"></i> ریسک‌های بحرانی</h3>
        <?php if (!empty($highRisks)): ?>
            <?php foreach ($highRisks as $risk): ?>
                <div class="list-item" style="gap: 12px;">
                    <div class="activity-icon" style="color:#EF4444;">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="list-item-info">
                        <a href="?controller=risk&action=show&id=<?= $risk['id'] ?>" class="list-item-link">
                            <?= htmlspecialchars($risk['title'] ?? 'ریسک بدون عنوان') ?>
                        </a>
                        <small class="text-muted">
                            <i class="fas fa-project-diagram"></i> <?= htmlspecialchars($risk['project_name'] ?? 'نامشخص') ?>
                            | امتیاز: <strong style="color:#EF4444;"><?= $risk['risk_score'] ?? 0 ?></strong>
                        </small>
                    </div>
                </div>
            <?php endforeach; ?>
            <div style="margin-top: 15px;">
                <a href="?controller=risk" class="btn btn-sm btn-danger">
                    <i class="fas fa-list"></i> مشاهده همه ریسک‌ها
                </a>
            </div>
        <?php else: ?>
            <p class="text-muted"><i class="fas fa-check-circle" style="color:#10B981;"></i> ریسک بحرانی وجود ندارد.</p>
        <?php endif; ?>
    </div>
</div>

<!-- پروژه‌های فعال و اخیر -->
<?php 
// ادغام هوشمند: اگر activeProjects بود از آن استفاده کن، در غیر این صورت recentProjects
$displayProjects = !empty($activeProjects) ? $activeProjects : $recentProjects;
?>

<?php if (!empty($displayProjects)): ?>
<div class="card" style="margin-top: 20px;">
    <h3 class="card-title">
        <i class="fas fa-clock"></i> 
        <?= !empty($activeProjects) ? 'پروژه‌های فعال' : 'پروژه‌های اخیر' ?>
    </h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>نام پروژه</th>
                    <th>متدولوژی</th>
                    <th>فاز</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($displayProjects as $project): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($project['name'] ?? 'پروژه بدون نام') ?></strong></td>
                    <td>
                        <span class="badge badge-info">
                            <?= pmbok_getMethodologyLabel($project['methodology'] ?? '') ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-<?= pmbok_getPhaseColor($project['phase'] ?? '') ?>">
                            <?= pmbok_getPhaseLabel($project['phase'] ?? '') ?>
                        </span>
                    </td>
                    <td>
                        <a href="?controller=project&action=show&id=<?= $project['id'] ?>" class="btn btn-sm btn-primary" title="مشاهده جزئیات">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- پیام برای کاربر مهمان -->
<?php if (!$isAuthenticated): ?>
<div class="card" style="margin-top: 20px; border-right: 5px solid var(--soft-warning); background: #FFFBEB;">
    <div style="display: flex; align-items: center; gap: 15px;">
        <div style="font-size: 2.5rem; color: var(--soft-warning);">
            <i class="fas fa-lock"></i>
        </div>
        <div style="flex: 1;">
            <h3 style="margin: 0 0 5px 0; font-size: 1.2rem;">برای مدیریت پروژه‌ها و ریسک‌ها وارد شوید</h3>
            <p style="margin: 0; color: var(--gray-dark); font-size: 0.95rem;">
                مشاهده حوزه‌های دانشی، فرآیندها و تکنیک‌ها برای همه آزاد است.
                اما برای ایجاد پروژه، ثبت ریسک و مدیریت تحویل‌دادنی‌ها باید وارد شوید.
            </p>
        </div>
        <a href="/login" class="btn btn-primary" style="white-space: nowrap;">
            <i class="fas fa-sign-in-alt"></i> ورود / ثبت‌نام
        </a>
    </div>
</div>
<?php endif; ?>

<!-- دسترسی سریع -->
<div class="card" style="margin-top: 20px;">
    <h3 class="card-title"><i class="fas fa-bolt"></i> دسترسی سریع</h3>
    <div class="quick-actions">
        <a href="?controller=project&action=create" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> ایجاد پروژه
        </a>
        <a href="?controller=risk&action=create" class="btn btn-danger">
            <i class="fas fa-exclamation-triangle"></i> ثبت ریسک
        </a>
        <a href="?controller=report" class="btn btn-success">
            <i class="fas fa-chart-bar"></i> گزارش‌ها
        </a>
        <a href="?controller=technique" class="btn btn-warning">
            <i class="fas fa-search"></i> جستجوی تکنیک
        </a>
    </div>
</div>