<?php
/**
 * سایدبار نرم‌افزار - نسخه پویا و توسعه‌پذیر (اصلاح شده)
 * در دسکتاپ عمودی / در موبایل افقی
 * 
 * برای اضافه کردن ماژول جدید:
 * 1. یک entry جدید به آرایه $moduleMenus اضافه کنید
 * 2. کلید باید همان moduleName باشد
 */

// دریافت route فعلی (پشتیبانی از هر دو الگو: route و controller)
$currentRoute = $_GET['controller'] ?? ($_GET['route'] ?? 'home');
$moduleName = $moduleName ?? 'babok';
$softwareName = $softwareName ?? 'Software';

// ✅ اصلاح شده: مستقیماً از CURRENT_MODULE_URL استفاده کن (بدون اضافه کردن /software/)
if (defined('CURRENT_MODULE_URL')) {
    $basePath = CURRENT_MODULE_URL;
} else {
    $basePath = '/software/' . $moduleName . '/';
}

// ============================================================
// تعریف منوهای هر ماژول
// ============================================================
$moduleMenus = [
    
    // -------- BABOK Analyzer --------
    'babok' => [
        'name' => 'BABOK Analyzer',
        'icon' => 'fas fa-robot',
        'param' => 'route',  // پارامتر URL
        'menus' => [
            ['route' => 'home',            'icon' => 'fas fa-home',       'label' => 'داشبورد'],
            ['route' => 'projects',        'icon' => 'fas fa-folder-open','label' => 'پروژه‌ها'],
            ['route' => 'tasks',           'icon' => 'fas fa-tasks',      'label' => 'وظایف'],
            ['route' => 'techniques',      'icon' => 'fas fa-tools',      'label' => 'تکنیک‌ها'],
            ['route' => 'knowledge_areas', 'icon' => 'fas fa-sitemap',    'label' => 'حوزه‌های دانشی'],
            ['route' => 'requirement',     'icon' => 'fas fa-robot',      'label' => 'استخراج و تحلیل'],
        ]
    ],
    
    // -------- PMBOK Analyzer --------
    'pmbok' => [
        'name' => 'PMBOK Analyzer',
        'icon' => 'fas fa-project-diagram',
        'param' => 'controller',  // پارامتر URL
        'menus' => [
            ['route' => 'dashboard',     'icon' => 'fas fa-home',                'label' => 'داشبورد'],
            ['route' => 'project',       'icon' => 'fas fa-folder-open',         'label' => 'پروژه‌ها'],
            ['route' => 'task',          'icon' => 'fas fa-tasks',               'label' => 'فرآیندها'],
            ['route' => 'knowledgeArea', 'icon' => 'fas fa-sitemap',             'label' => 'حوزه‌های دانشی'],
            ['route' => 'technique',     'icon' => 'fas fa-tools',               'label' => 'تکنیک‌ها'],
            ['route' => 'risk',          'icon' => 'fas fa-exclamation-triangle','label' => 'ریسک‌ها'],
            ['route' => 'report',        'icon' => 'fas fa-chart-bar',           'label' => 'گزارش‌ها'],
        ]
    ],
    
    // -------- الگو برای ماژول‌های آینده --------
    // 'module-name' => [
    //     'name' => 'Module Name',
    //     'icon' => 'fas fa-icon',
    //     'param' => 'controller',
    //     'menus' => [
    //         ['route' => 'home',  'icon' => 'fas fa-home', 'label' => 'داشبورد'],
    //     ]
    // ],
];

// ============================================================
// دریافت تنظیمات ماژول فعلی
// ============================================================
$currentModule = $moduleMenus[$moduleName] ?? [
    'name' => $softwareName,
    'icon' => 'fas fa-cube',
    'param' => 'route',
    'menus' => [
        ['route' => 'home', 'icon' => 'fas fa-home', 'label' => 'داشبورد'],
    ]
];

$menuItems = $currentModule['menus'];
$menuParam = $currentModule['param'];
$moduleIcon = $currentModule['icon'];
$displayName = $currentModule['name'];

// ✅ اصلاح فعال بودن منو برای route های خاص
$activeRoutes = [];
foreach ($menuItems as $item) {
    $activeRoutes[] = $item['route'];
}
$isActiveRoute = in_array($currentRoute, $activeRoutes);
?>

<!-- هدر سایدبار -->
<div class="software-sidebar-header">
    <h3><i class="<?php echo $moduleIcon; ?>"></i> <?php echo htmlspecialchars($displayName); ?></h3>
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
        <?php 
        $isActive = ($currentRoute === $item['route']);
        $url = $basePath . '?' . $menuParam . '=' . $item['route'];
        ?>
        <a href="<?php echo $url; ?>" class="<?php echo $isActive ? 'active' : ''; ?>">
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