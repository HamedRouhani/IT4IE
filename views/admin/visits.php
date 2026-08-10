<?php
// آماده‌سازی داده‌های نمودار (پر کردن روزهای خالی)
$days = [];
for ($i = 13; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $days[$d] = ['visits' => 0, 'unique_ips' => 0];
}
foreach ($daily as $row) {
    if (isset($days[$row['visit_date']])) {
        $days[$row['visit_date']] = [
            'visits' => (int) $row['visits'],
            'unique_ips' => (int) $row['unique_ips']
        ];
    }
}
$maxVisits = max(array_merge([1], array_column($days, 'visits')));

// تشخیص مرورگر و دستگاه
function detectBrowser($ua) {
    if (!$ua) return 'نامشخص';
    if (strpos($ua, 'Edg') !== false) return 'Edge';
    if (strpos($ua, 'Chrome') !== false) return 'Chrome';
    if (strpos($ua, 'Firefox') !== false) return 'Firefox';
    if (strpos($ua, 'Safari') !== false) return 'Safari';
    if (strpos($ua, 'Opera') !== false) return 'Opera';
    return 'سایر';
}
function detectDevice($ua) {
    if (!$ua) return 'نامشخص';
    if (preg_match('/Mobile|Android|iPhone/i', $ua)) return '📱 موبایل';
    if (preg_match('/Tablet|iPad/i', $ua)) return '💻 تبلت';
    return '🖥️ دسکتاپ';
}
?>

<div class="admin-container">
    
    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>
    
    <!-- محتوا -->
    <div class="admin-content">
        <div class="admin-header">
            <h1>📊 آمار بازدید سایت</h1>
            <span>مشاهده وضعیت بازدید کاربران</span>
        </div>
        
        <!-- کارت‌های آمار -->
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-eye"></i></div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['total_visits'] ?? 0); ?></h3>
                    <p>کل بازدیدها</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-calendar-day"></i></div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['today_visits'] ?? 0); ?></h3>
                    <p>بازدید امروز</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="fas fa-chart-line"></i></div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['week_visits'] ?? 0); ?></h3>
                    <p>۷ روز اخیر</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['unique_ips'] ?? 0); ?></h3>
                    <p>بازدیدکننده یکتا</p>
                </div>
            </div>
        </div>
        
        <!-- نمودار ۱۴ روز اخیر -->
        <div class="admin-widget" style="margin-bottom: 20px;">
            <h3><i class="fas fa-chart-bar"></i> نمودار بازدید ۱۴ روز اخیر</h3>
            <div class="visits-chart">
                <?php foreach ($days as $date => $data): ?>
                    <div class="chart-bar-wrap" title="<?php echo $date; ?> — <?php echo $data['visits']; ?> بازدید">
                        <div class="chart-bar" style="height: <?php echo max(3, round(($data['visits'] / $maxVisits) * 100)); ?>%;"></div>
                        <div class="chart-label"><?php echo date('d', strtotime($date)); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- دو ویجت: پربازدیدها + منابع -->
        <div class="admin-widgets">
            <div class="admin-widget">
                <h3><i class="fas fa-star"></i> پربازدیدترین صفحات</h3>
                <?php if (empty($topPages)): ?>
                    <div class="empty-state"><i class="fas fa-inbox"></i>هنوز بازدیدی ثبت نشده است.</div>
                <?php else: ?>
                    <ul>
                        <?php foreach ($topPages as $page): ?>
                            <li>
                                <span><code><?php echo htmlspecialchars($page['page_url']); ?></code></span>
                                <span class="widget-date"><?php echo number_format($page['visits']); ?> بازدید</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            
            <div class="admin-widget">
                <h3><i class="fas fa-link"></i> منابع ورود</h3>
                <?php if (empty($referrers)): ?>
                    <div class="empty-state"><i class="fas fa-link"></i>داده‌ای ثبت نشده است.</div>
                <?php else: ?>
                    <ul>
                        <?php foreach ($referrers as $ref): ?>
                            <li>
                                <span style="word-break: break-all;"><?php echo htmlspecialchars($ref['referrer']); ?></span>
                                <span class="widget-date"><?php echo number_format($ref['visits']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- آخرین بازدیدها -->
        <div class="admin-table" style="margin-top: 20px;">
            <table>
                <thead>
                    <tr>
                        <th>صفحه</th>
                        <th>کاربر</th>
                        <th>IP</th>
                        <th>مرورگر</th>
                        <th>دستگاه</th>
                        <th>تاریخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent)): ?>
                        <tr><td colspan="6" style="text-align:center;">هنوز بازدیدی ثبت نشده است. با بازدید از صفحات سایت، داده‌ها ثبت می‌شوند.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recent as $visit): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars(mb_substr($visit['page_url'], 0, 40)); ?></code></td>
                            <td>
                                <?php if (!empty($visit['user_name'])): ?>
                                    <span class="status-badge replied"><?php echo htmlspecialchars($visit['user_name']); ?></span>
                                <?php else: ?>
                                    <span class="status-badge read">مهمان</span>
                                <?php endif; ?>
                            </td>
                            <td><code><?php echo htmlspecialchars($visit['ip_address']); ?></code></td>
                            <td><?php echo detectBrowser($visit['user_agent']); ?></td>
                            <td><?php echo detectDevice($visit['user_agent']); ?></td>
                            <td><?php echo $visit['visit_date'] . ' ' . substr($visit['visit_time'], 0, 5); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>