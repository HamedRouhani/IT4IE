<?php
/**
 * سایدبار نرم‌افزار - نسخه پویا و توسعه‌پذیر
 * در دسکتاپ عمودی / در موبایل افقی
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
            ['route' => 'search',          'icon' => 'fas fa-search',     'label' => 'جستجوی هوشمند'],
            ['route' => 'reports',         'icon' => 'fas fa-chart-bar', 'label' => 'گزارش‌های هوشمند'],
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
    
    // -------- MCDM Analyzer --------
    'mcdm' => [
        'name'  => 'MCDM Analyzer',
        'icon'  => 'fas fa-balance-scale',
        'param' => 'controller',
        'menus' => [
            ['route' => 'dashboard',     'icon' => 'fas fa-home',              'label' => 'داشبورد'],
            ['route' => 'project',       'icon' => 'fas fa-folder-open',       'label' => 'پروژه‌های تصمیم‌گیری'],
            ['route' => 'method',        'icon' => 'fas fa-calculator',        'label' => 'روش‌های MCDM'],
            ['route' => 'knowledgearea', 'icon' => 'fas fa-sitemap',           'label' => 'حوزه‌های دانشی'],
            ['route' => 'industry',      'icon' => 'fas fa-industry',          'label' => 'صنایع'],
            ['route' => 'assistant',     'icon' => 'fas fa-robot',             'label' => 'دستیار هوشمند'],
            ['route' => 'report',        'icon' => 'fas fa-chart-bar',         'label' => 'گزارش‌ها'],
        ]
    ],

    // -------- OR Analyzer --------
    'or' => [
        'name'  => 'OR Analyzer',
        'icon'  => 'fas fa-square-root-alt',
        'param' => 'controller',
        'menus' => [
            ['route' => 'dashboard',     'icon' => 'fas fa-home',              'label' => 'داشبورد'],
            ['route' => 'smart_modeler', 'icon' => 'fas fa-brain',             'label' => 'مدلسازی هوشمند'],
            ['route' => 'transport',     'icon' => 'fas fa-truck',             'label' => 'حمل و نقل'],
            ['route' => 'assignment',    'icon' => 'fas fa-users-cog',         'label' => 'تخصیص'],
            ['route' => 'transship',     'icon' => 'fas fa-project-diagram',   'label' => 'ترانشیپمنت'],
            ['route' => 'shortest',      'icon' => 'fas fa-route',             'label' => 'کوتاه‌ترین مسیر'],
            ['route' => 'simplex',       'icon' => 'fas fa-chart-line',        'label' => 'برنامه‌ریزی خطی'],
            ['route' => 'sensitivity',   'icon' => 'fas fa-sliders-h',         'label' => 'تحلیل حساسیت'],
            ['route' => 'problem_type',  'icon' => 'fas fa-cubes',             'label' => 'انواع مسئله'],
            ['route' => 'method',        'icon' => 'fas fa-calculator',        'label' => 'روش‌های حل'],
            ['route' => 'report',        'icon' => 'fas fa-chart-bar',         'label' => 'گزارش‌ها'],
        ]
    ],
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
            
            <!-- 🔔 آیکون اعلان‌ها (فقط برای ماژول babok) -->
            <?php if (($moduleName ?? '') === 'babok'): ?>
                <?php
                // دریافت تعداد اعلان‌های خوانده‌نشده
                $notifCount = 0;
                if (isset($_SESSION['user_id'])) {
                    try {
                        $db = \App\Core\Database::getInstance();
                        $stmt = $db->prepare("SELECT COUNT(*) FROM babok_notifications WHERE user_id = ? AND is_read = 0");
                        $stmt->execute([$_SESSION['user_id']]);
                        $notifCount = (int)$stmt->fetchColumn();
                    } catch (\Exception $e) {
                        $notifCount = 0;
                    }
                }
                ?>
                <a href="?route=notifications" style="position: relative; margin-right: 10px; color: inherit; text-decoration: none;" title="اعلان‌ها">
                    <i class="fas fa-bell" style="font-size: 1.1rem;"></i>
                    <?php if ($notifCount > 0): ?>
                        <span class="notification-badge" style="
                            position: absolute;
                            top: -6px;
                            right: -8px;
                            background: #ef4444;
                            color: white;
                            font-size: 0.65rem;
                            font-weight: 700;
                            padding: 2px 5px;
                            border-radius: 99px;
                            min-width: 16px;
                            text-align: center;
                            line-height: 1;
                            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.4);
                        ">
                            <?= $notifCount > 99 ? '99+' : $notifCount ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
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