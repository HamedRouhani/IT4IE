<div class="admin-container">
    <!-- Sidebar -->
    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>
    
    <!-- Content -->
    <div class="admin-content">
        <div class="admin-header">
            <h1>📊 داشبورد</h1>
            <span><?php echo $_SESSION['user_name'] ?? 'مدیر'; ?> عزیز خوش آمدید</span>
        </div>
        
        <!-- Stats -->
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-file-alt"></i></div>
                <div class="stat-info">
                    <h3><?php echo $totalPosts ?? 0; ?></h3>
                    <p>تعداد پست‌ها</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="fas fa-envelope"></i></div>
                <div class="stat-info">
                    <h3><?php echo $totalMessages ?? 0; ?></h3>
                    <p>پیام‌های خوانده نشده</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h3><?php echo $totalUsers ?? 0; ?></h3>
                    <p>کاربران فعال</p>
                </div>
            </div>
            
            <a href="/admin/visits" style="text-decoration: none;">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-info">
                        <h3><?php echo number_format($todayVisits ?? 0); ?></h3>
                        <p>بازدید امروز</p>
                    </div>
                </div>
            </a>
            
            <a href="/admin/visits" style="text-decoration: none;">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-eye"></i></div>
                    <div class="stat-info">
                        <h3><?php echo number_format($totalVisits ?? 0); ?></h3>
                        <p>کل بازدیدها</p>
                    </div>
                </div>
            </a>
        </div>
        
        <!-- Widgets -->
        <div class="admin-widgets">
            <!-- Recent Posts -->
            <div class="admin-widget">
                <h3><i class="fas fa-file-alt"></i> آخرین پست‌ها</h3>
                <?php if (empty($recentPosts)): ?>
                    <div class="empty-state">
                        <i class="fas fa-file-alt"></i>
                        <p>هیچ پستی وجود ندارد.</p>
                    </div>
                <?php else: ?>
                    <ul>
                        <?php foreach ($recentPosts as $post): ?>
                            <li>
                                <a href="/admin/posts/edit/<?php echo $post['id']; ?>" style="color: var(--dark);">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                                <span class="widget-date"><?php echo jdate($post['created_at']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            
            <!-- Recent Messages -->
            <div class="admin-widget">
                <h3><i class="fas fa-envelope"></i> آخرین پیام‌ها</h3>
                <?php if (empty($recentMessages)): ?>
                    <div class="empty-state">
                        <i class="fas fa-envelope"></i>
                        <p>هیچ پیامی وجود ندارد.</p>
                    </div>
                <?php else: ?>
                    <ul>
                        <?php foreach ($recentMessages as $message): ?>
                            <li>
                                <span>
                                    <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                    <span style="color: var(--gray); font-size: 0.8rem;"> - <?php echo htmlspecialchars($message['subject']); ?></span>
                                </span>
                                <span class="widget-date"><?php echo jdate($message['created_at']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>