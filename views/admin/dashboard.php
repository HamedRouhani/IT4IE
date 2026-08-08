<div class="admin-container">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <h3>📊 مدیریت</h3>
            <span>پنل مدیریت IT4IE</span>
        </div>
        <ul>
            <li><a href="/admin" class="active"><i class="fas fa-tachometer-alt"></i> داشبورد</a></li>
            <li><a href="/admin/posts"><i class="fas fa-file-alt"></i> پست‌ها</a></li>
            <li><a href="/admin/messages"><i class="fas fa-envelope"></i> پیام‌ها</a></li>
            <li><a href="/admin/settings"><i class="fas fa-cog"></i> تنظیمات</a></li>
            <li><a href="/"><i class="fas fa-home"></i> بازگشت به سایت</a></li>
        </ul>
    </aside>
    
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