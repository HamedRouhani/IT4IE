<?php
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
?>
<aside class="admin-sidebar">
    <div class="admin-brand">
        <h3>📊 مدیریت</h3>
        <span>پنل مدیریت IT4IE</span>
    </div>
    <ul>
        <li>
            <a href="/admin" class="<?php echo ($currentUri === '/admin' || $currentUri === '/admin/') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> داشبورد
            </a>
        </li>
        <li>
            <a href="/admin/posts" class="<?php echo (strpos($currentUri, '/admin/posts') === 0) ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i> پست‌ها
            </a>
        </li>
                <li>
            <a href="/admin/categories" class="<?php echo (strpos($currentUri, '/admin/categories') === 0) ? 'active' : ''; ?>">
                <i class="fas fa-layer-group"></i> دسته‌بندی‌ها
            </a>
        </li>
        <li>
            <a href="/admin/messages" class="<?php echo (strpos($currentUri, '/admin/messages') === 0) ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i> پیام‌ها
            </a>
        </li>
        <li>
            <a href="/admin/users" class="<?php echo (strpos($currentUri, '/admin/users') === 0) ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> کاربران
            </a>
        </li>
        <li>
            <a href="/admin/visits" class="<?php echo (strpos($currentUri, '/admin/visits') === 0) ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> آمار بازدید
            </a>
        </li>
        <li>
            <a href="/admin/software-activity" class="<?php echo (strpos($currentUri, '/admin/software-activity') === 0) ? 'active' : ''; ?>">
                <i class="fas fa-cubes"></i> آمار نرم‌افزارها
            </a>
        </li>
        <li>
            <a href="/admin/settings" class="<?php echo (strpos($currentUri, '/admin/settings') === 0) ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> تنظیمات
            </a>
        </li>
        <li>
            <a href="/">
                <i class="fas fa-home"></i> بازگشت به سایت
            </a>
        </li>
    </ul>
</aside>