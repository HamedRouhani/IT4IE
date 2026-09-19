<?php
// فقط مسیر، بدون کوئری‌استرینگ
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// فعال بودن آیتم «ارسال‌های چک‌لیست»
$checklistSubmissionsActive = (
    $currentPath === '/admin/checklist' ||
    strpos($currentPath, '/admin/checklist/view') === 0 ||
    strpos($currentPath, '/admin/checklist/message') === 0 ||
    strpos($currentPath, '/admin/checklist/update-status') === 0
);

// فعال بودن آیتم «مدیریت چک‌لیست‌ها»
$checklistManageActive = (
    $currentPath === '/admin/checklists' ||
    strpos($currentPath, '/admin/checklists/') === 0 ||
    strpos($currentPath, '/admin/checklist/create') === 0 ||
    strpos($currentPath, '/admin/checklist/edit') === 0 ||
    strpos($currentPath, '/admin/checklist/delete') === 0 ||
    strpos($currentPath, '/admin/checklist/questions') === 0 ||
    strpos($currentPath, '/admin/checklist/question') === 0
);
?>
<aside class="admin-sidebar">
    <div class="admin-brand">
        <h3>📊 مدیریت</h3>
        <span>پنل مدیریت IT4IE</span>
    </div>
    <ul>
        <li>
            <a href="/admin" class="<?php echo ($currentPath === '/admin' || $currentPath === '/admin/') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> داشبورد
            </a>
        </li>
        <li>
            <a href="/admin/posts" class="<?php echo (strpos($currentPath, '/admin/posts') === 0) ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i> پست‌ها
            </a>
        </li>
        <li>
            <a href="/admin/categories" class="<?php echo (strpos($currentPath, '/admin/categories') === 0) ? 'active' : ''; ?>">
                <i class="fas fa-layer-group"></i> دسته‌بندی‌ها
            </a>
        </li>
        <li>
            <a href="/admin/messages" class="<?php echo (strpos($currentPath, '/admin/messages') === 0) ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i> پیام‌ها
            </a>
        </li>
        <li>
            <a href="/admin/checklist" class="<?php echo $checklistSubmissionsActive ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i> ارسال‌های چک‌لیست
            </a>
        </li>
        <li>
            <a href="/admin/checklists" class="<?php echo $checklistManageActive ? 'active' : ''; ?>">
                <i class="fas fa-tasks"></i> مدیریت چک‌لیست‌ها
            </a>
        </li>
        <li>
            <a href="/admin/payments" class="<?php echo (strpos($currentPath, '/admin/payments') === 0) ? 'active' : ''; ?>">
                <i class="fas fa-credit-card"></i> پرداخت‌ها
            </a>
        </li>
        <li>
            <a href="/admin/plans"
            class="<?php echo (strpos($currentPath, '/admin/plans') === 0) ? 'active' : ''; ?>">
                <i class="fas fa-tags"></i> طرح‌های اشتراک
            </a>
        </li>
        <li>
            <a href="/admin/vouchers" class="<?php echo (strpos($currentPath, '/admin/vouchers') === 0) ? 'active' : ''; ?>">
                <i class="fas fa-ticket-alt"></i> کدهای اشتراک
            </a>
        </li>
        <li>
            <a href="/admin/leads" class="<?php echo (strpos($currentPath, '/admin/leads') === 0) ? 'active' : ''; ?>">
                <i class="fas fa-bullseye"></i> لیدها
                <?php
                try {
                    $leadModel = new \App\Models\Subscription();
                    $hotLeads = count($leadModel->getLeads(100, 75, 'new'));
                    if ($hotLeads > 0): ?>
                        <span class="nav-badge hot"><?= $hotLeads ?></span>
                <?php endif;
                } catch (\Throwable $e) {}
                ?>
            </a>
        </li>
        <li>
            <a href="/admin/users" class="<?php echo (strpos($currentPath, '/admin/users') === 0) ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> کاربران
            </a>
        </li>
        <li>
            <a href="/admin/visits" class="<?php echo (strpos($currentPath, '/admin/visits') === 0) ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> آمار بازدید
            </a>
        </li>
        <li>
            <a href="/admin/software-activity" class="<?php echo (strpos($currentPath, '/admin/software-activity') === 0) ? 'active' : ''; ?>">
                <i class="fas fa-cubes"></i> آمار نرم‌افزارها
            </a>
        </li>
        <li>
            <a href="/admin/settings" class="<?php echo (strpos($currentPath, '/admin/settings') === 0) ? 'active' : ''; ?>">
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