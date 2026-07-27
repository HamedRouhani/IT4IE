<div class="admin-container">
    <div class="admin-sidebar">
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
    </div>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>📊 داشبورد</h1>
            <span><?php echo $_SESSION['user_name'] ?? 'مدیر'; ?> عزیز خوش آمدید</span>
        </div>
        
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
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: var(--shadow);">
                <h3>📝 آخرین پست‌ها</h3>
                <?php if (empty($recentPosts)): ?>
                    <p style="color: var(--gray);">هیچ پستی وجود ندارد.</p>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($recentPosts as $post): ?>
                            <li style="padding: 8px 0; border-bottom: 1px solid var(--gray-light);">
                                <a href="/admin/posts/edit/<?php echo $post['id']; ?>">
                                    <?php echo $post['title']; ?>
                                </a>
                                <span style="font-size: 12px; color: var(--gray);">
                                    <?php echo jdate($post['created_at']); ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: var(--shadow);">
                <h3>✉️ آخرین پیام‌ها</h3>
                <?php if (empty($recentMessages)): ?>
                    <p style="color: var(--gray);">هیچ پیامی وجود ندارد.</p>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($recentMessages as $message): ?>
                            <li style="padding: 8px 0; border-bottom: 1px solid var(--gray-light);">
                                <strong><?php echo $message['name']; ?></strong>
                                <span style="font-size: 12px; color: var(--gray);">
                                    <?php echo $message['subject']; ?>
                                </span>
                                <span style="font-size: 12px; color: var(--gray);">
                                    <?php echo jdate($message['created_at']); ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>