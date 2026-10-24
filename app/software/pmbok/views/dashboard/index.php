<?php
/**
 * داشبورد اصلی ماژول PMBOK Analyzer (نسخهٔ بازطراحی‌شده)
 * بدون استایل inline؛ همهٔ استایل‌ها در pmbok.css
 */

$pageTitle       = $pageTitle ?? 'داشبورد - PMBOK Analyzer';
$activePage      = $activePage ?? 'dashboard';
$isAuthenticated = $isAuthenticated ?? false;

$stats          = is_array($stats ?? null) ? $stats : [];
$knowledgeAreas = $knowledgeAreas ?? [];
$highRisks      = $highRisks ?? [];
$recentProjects = $recentProjects ?? [];
$activeProjects = $activeProjects ?? [];

$statCards = [
    ['key' => 'knowledge_areas', 'icon' => 'fa-sitemap',             'label' => 'حوزه‌های دانشی', 'tone' => 'primary'],
    ['key' => 'tasks',           'icon' => 'fa-tasks',               'label' => 'فرآیندها (49)',  'tone' => 'success'],
    ['key' => 'techniques',      'icon' => 'fa-microchip',           'label' => 'تکنیک‌ها',        'tone' => 'warning'],
    ['key' => 'projects',        'icon' => 'fa-rocket',              'label' => 'پروژه‌ها',        'tone' => 'info'],
    ['key' => 'risks',           'icon' => 'fa-exclamation-triangle','label' => 'ریسک‌ها',         'tone' => 'danger'],
    ['key' => 'deliverables',    'icon' => 'fa-box',                 'label' => 'تحویل‌دادنی‌ها',  'tone' => 'neutral'],
];

// اگر پروژهٔ فعال بود همان را نشان بده، وگرنه پروژه‌های اخیر
$displayProjects = !empty($activeProjects) ? $activeProjects : $recentProjects;
?>

<!-- کارت‌های آمار -->
<div class="stats-grid">
    <?php foreach ($statCards as $s): ?>
        <div class="card stat-card stat-card--<?= $s['tone'] ?>">
            <div class="stat-icon" aria-hidden="true"><i class="fas <?= $s['icon'] ?>"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= (int)($stats[$s['key']] ?? 0) ?></div>
                <div class="stat-label"><?= $s['label'] ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="main-grid">
    <!-- حوزه‌های دانشی -->
    <section class="card">
        <h3 class="card-title"><i class="fas fa-diagram-project"></i> حوزه‌های دانشی PMBOK</h3>
        <?php if (!empty($knowledgeAreas)): ?>
            <ul class="list-items">
                <?php foreach ($knowledgeAreas as $area): ?>
                    <li class="list-item">
                        <a href="?controller=knowledgeArea&action=show&id=<?= (int)$area['id'] ?>" class="list-item-link">
                            <i class="fas fa-folder"></i>
                            <span><?= htmlspecialchars($area['name'] ?? 'نامشخص') ?></span>
                        </a>
                        <span class="badge badge-primary"><?= (int)($area['task_count'] ?? 0) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="card-footer">
                <a href="?controller=knowledgeArea" class="btn btn-sm btn-primary">
                    <i class="fas fa-arrow-left"></i> مشاهده همه
                </a>
            </div>
        <?php else: ?>
            <p class="text-muted">هیچ حوزه‌ای یافت نشد.</p>
        <?php endif; ?>
    </section>

    <!-- ریسک‌های بحرانی -->
    <section class="card">
        <h3 class="card-title"><i class="fas fa-exclamation-triangle text-danger"></i> ریسک‌های بحرانی</h3>
        <?php if (!empty($highRisks)): ?>
            <ul class="list-items">
                <?php foreach ($highRisks as $risk): ?>
                    <li class="list-item list-item--start">
                        <span class="text-danger" aria-hidden="true"><i class="fas fa-exclamation-circle"></i></span>
                        <div class="list-item-info">
                            <a href="?controller=risk&action=show&id=<?= (int)$risk['id'] ?>" class="list-item-link">
                                <?= htmlspecialchars($risk['title'] ?? 'ریسک بدون عنوان') ?>
                            </a>
                            <small class="text-muted">
                                <i class="fas fa-project-diagram"></i> <?= htmlspecialchars($risk['project_name'] ?? 'نامشخص') ?>
                                · امتیاز: <strong class="text-danger"><?= (int)($risk['risk_score'] ?? 0) ?></strong>
                            </small>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="card-footer">
                <a href="?controller=risk" class="btn btn-sm btn-danger">
                    <i class="fas fa-list"></i> مشاهده همه ریسک‌ها
                </a>
            </div>
        <?php else: ?>
            <p class="text-muted"><i class="fas fa-check-circle text-success"></i> ریسک بحرانی وجود ندارد.</p>
        <?php endif; ?>
    </section>
</div>

<?php if (!empty($displayProjects)): ?>
<!-- پروژه‌های فعال / اخیر -->
<section class="card">
    <h3 class="card-title">
        <i class="fas fa-clock"></i>
        <?= !empty($activeProjects) ? 'پروژه‌های فعال' : 'پروژه‌های اخیر' ?>
    </h3>
    <div class="table-responsive">
        <table class="table table-hover">
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
                    <td><span class="badge badge-info"><?= pmbok_getMethodologyLabel($project['methodology'] ?? '') ?></span></td>
                    <td>
                        <span class="badge badge-<?= pmbok_getPhaseColor($project['phase'] ?? '') ?>">
                            <?= pmbok_getPhaseLabel($project['phase'] ?? '') ?>
                        </span>
                    </td>
                    <td>
                        <a href="?controller=project&action=show&id=<?= (int)$project['id'] ?>"
                           class="btn btn-sm btn-primary" title="مشاهده جزئیات" aria-label="مشاهده جزئیات">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endif; ?>

<?php if (!$isAuthenticated): ?>
<!-- پیام برای کاربر مهمان -->
<aside class="card notice notice--warning">
    <div class="notice-icon" aria-hidden="true"><i class="fas fa-lock"></i></div>
    <div class="notice-body">
        <h3>برای مدیریت پروژه‌ها و ریسک‌ها وارد شوید</h3>
        <p>
            مشاهدهٔ حوزه‌های دانشی، فرآیندها و تکنیک‌ها برای همه آزاد است.
            اما برای ایجاد پروژه، ثبت ریسک و مدیریت تحویل‌دادنی‌ها باید وارد شوید.
        </p>
    </div>
    <a href="/login" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> ورود / ثبت‌نام</a>
</aside>
<?php endif; ?>

<!-- دسترسی سریع -->
<section class="card">
    <h3 class="card-title"><i class="fas fa-bolt"></i> دسترسی سریع</h3>
    <div class="quick-actions">
        <a href="?controller=project&action=create" class="btn btn-primary"><i class="fas fa-plus-circle"></i> ایجاد پروژه</a>
        <a href="?controller=risk&action=create" class="btn btn-danger"><i class="fas fa-exclamation-triangle"></i> ثبت ریسک</a>
        <a href="?controller=report" class="btn btn-success"><i class="fas fa-chart-bar"></i> گزارش‌ها</a>
        <a href="?controller=technique" class="btn btn-warning"><i class="fas fa-search"></i> جستجوی تکنیک</a>
    </div>
</section>
