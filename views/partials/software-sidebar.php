<?php
/**
 * سایدبار نرم‌افزار - یک بار رندر می‌شود
 * در دسکتاپ عمودی / در موبایل افقی (توسط CSS)
 */
$currentRoute = $_GET['route'] ?? 'home';
$moduleName = $moduleName ?? 'babok';
$softwareName = $softwareName ?? 'BABOK Analyzer';
$basePath = '/software/' . $moduleName . '/';

$menuItems = [
    ['route' => 'home',            'icon' => 'fas fa-home',      'label' => 'داشبورد'],
    ['route' => 'projects',        'icon' => 'fas fa-folder-open','label' => 'پروژه‌ها'],
    ['route' => 'tasks',           'icon' => 'fas fa-tasks',      'label' => 'وظایف'],
    ['route' => 'techniques',      'icon' => 'fas fa-tools',      'label' => 'تکنیک‌ها'],
    ['route' => 'knowledge_areas', 'icon' => 'fas fa-sitemap',    'label' => 'حوزه‌های دانشی'],
    ['route' => 'requirement',     'icon' => 'fas fa-robot',      'label' => 'استخراج و تحلیل'],
];

$moduleIcon = ($moduleName === 'babok') ? 'fas fa-robot' : 'fas fa-chart-line';
?>

<!-- هدر سایدبار -->
<div class="software-sidebar-header">
    <h3><i class="<?php echo $moduleIcon; ?>"></i> <?php echo htmlspecialchars($softwareName); ?></h3>
    <div class="software-subtitle">
        <?php if (isset($_SESSION['user_name'])): ?>
            <i class="fas fa-user-circle"></i>
            <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        <?php else: ?>
            <i class="fas fa-eye"></i>
            <span>حالت مهمان</span>
        <?php endif; ?>
    </div>
</div>

<!-- منوی اصلی -->
<nav class="software-nav">
    <?php foreach ($menuItems as $item): ?>
        <a href="<?php echo $basePath; ?>?route=<?php echo $item['route']; ?>"
           class="<?php echo ($currentRoute === $item['route']) ? 'active' : ''; ?>">
            <i class="<?php echo $item['icon']; ?>"></i>
            <span><?php echo htmlspecialchars($item['label']); ?></span>
        </a>
    <?php endforeach; ?>
</nav>

<!-- فوتر سایدبار -->
<div class="software-sidebar-footer">
    <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="/login" class="btn-login-software">
            <i class="fas fa-sign-in-alt"></i>
            <span>ورود / ثبت‌نام</span>
        </a>
    <?php endif; ?>
    <a href="/software" class="btn-exit-software">
        <i class="fas fa-sign-out-alt"></i>
        <span>خروج از نرم‌افزار</span>
    </a>
</div>