<?php
require_once APP_PATH . '/models/SoftwareActivityLog.php';
$logModel = new \App\Models\SoftwareActivityLog();
?>

<div class="admin-container">
    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>📊 آمار استفاده از نرم‌افزارها</h1>
            <span>گزارش فعالیت‌های کاربران در نرم‌افزارها</span>
        </div>
        
        <!-- فیلتر نرم‌افزار -->
        <div class="admin-widget" style="margin-bottom: 20px;">
            <h3><i class="fas fa-filter"></i> فیلتر</h3>
            <form method="GET" action="/admin/software-activity" style="display: flex; gap: 10px; margin-top: 10px;">
                <select name="software" style="flex: 1; padding: 10px; border: 2px solid var(--gray-light); border-radius: var(--radius);">
                    <option value="">همه نرم‌افزارها</option>
                    <?php 
                    $softwareModel = new \App\Models\Software();
                    $softwareList = $softwareModel->getActiveSoftware();
                    foreach ($softwareList as $sw): 
                    ?>
                        <option value="<?php echo htmlspecialchars($sw['slug']); ?>" 
                                <?php echo (($currentSoftware ?? '') === $sw['slug']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($sw['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-admin-submit">اعمال فیلتر</button>
            </form>
        </div>
        
        <!-- آمار کلی -->
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-list"></i></div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['total_activities'] ?? 0); ?></h3>
                    <p>کل فعالیت‌ها</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['unique_users'] ?? 0); ?></h3>
                    <p>کاربران یکتا</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="fas fa-globe"></i></div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['unique_ips'] ?? 0); ?></h3>
                    <p>IP های یکتا</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-user-secret"></i></div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['guest_activities'] ?? 0); ?></h3>
                    <p>فعالیت مهمان</p>
                </div>
            </div>
        </div>
        
        <!-- آمار بر اساس نوع فعالیت -->
        <?php if (!empty($statsByAction)): ?>
        <div class="admin-widget" style="margin-bottom: 20px;">
            <h3><i class="fas fa-chart-pie"></i> آمار بر اساس نوع فعالیت</h3>
            <div class="admin-table" style="margin-top: 10px;">
                <table>
                    <thead>
                        <tr>
                            <th>نوع فعالیت</th>
                            <th>تعداد</th>
                            <th>درصد</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalActions = array_sum(array_column($statsByAction, 'count'));
                        foreach ($statsByAction as $actionStat): 
                            $percentage = $totalActions > 0 ? round(($actionStat['count'] / $totalActions) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td><span class="status-badge unread"><?php echo htmlspecialchars($actionStat['action']); ?></span></td>
                            <td><strong><?php echo number_format($actionStat['count']); ?></strong></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="flex: 1; height: 8px; background: var(--gray-light); border-radius: 10px; overflow: hidden;">
                                        <div style="width: <?php echo $percentage; ?>%; height: 100%; background: var(--primary);"></div>
                                    </div>
                                    <span><?php echo $percentage; ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- لیست لاگ‌ها -->
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>فعالیت</th>
                        <th>کاربر</th>
                        <th>IP</th>
                        <th>نوع رکورد</th>
                        <th>تاریخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="6" style="text-align:center;">هیچ لاگی ثبت نشده است.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $index => $log): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><span class="status-badge unread"><?php echo htmlspecialchars($log['action'] ?? ''); ?></span></td>
                            <td>
                                <?php if (!empty($log['user_name_from_db'])): ?>
                                    <strong><?php echo htmlspecialchars($log['user_name_from_db']); ?></strong>
                                <?php elseif (!empty($log['user_name'])): ?>
                                    <?php echo htmlspecialchars($log['user_name']); ?>
                                <?php else: ?>
                                    <span class="status-badge read">مهمان</span>
                                <?php endif; ?>
                            </td>
                            <td><code><?php echo htmlspecialchars($log['ip_address'] ?? ''); ?></code></td>
                            <td>
                                <?php if (!empty($log['record_type'])): ?>
                                    <span class="status-badge draft"><?php echo htmlspecialchars($log['record_type']); ?></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('Y/m/d H:i', strtotime($log['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>