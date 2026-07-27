<div class="admin-container">
    <div class="admin-sidebar">
        <div class="admin-brand">
            <h3>📊 مدیریت</h3>
            <span>پنل مدیریت IT4IE</span>
        </div>
        <ul>
            <li><a href="/admin"><i class="fas fa-tachometer-alt"></i> داشبورد</a></li>
            <li><a href="/admin/posts" class="active"><i class="fas fa-file-alt"></i> پست‌ها</a></li>
            <li><a href="/admin/messages"><i class="fas fa-envelope"></i> پیام‌ها</a></li>
            <li><a href="/admin/settings"><i class="fas fa-cog"></i> تنظیمات</a></li>
            <li><a href="/"><i class="fas fa-home"></i> بازگشت به سایت</a></li>
        </ul>
    </div>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>📝 مدیریت پست‌ها</h1>
            <a href="/admin/posts/create" class="btn-register" style="padding: 8px 16px; font-size: 14px; text-decoration: none;">
                <i class="fas fa-plus"></i> پست جدید
            </a>
        </div>
        
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>عنوان</th>
                        <th>دسته‌بندی</th>
                        <th>وضعیت</th>
                        <th>تاریخ</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($posts)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--gray);">
                                هنوز پستی وجود ندارد.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td><?php echo $post['id']; ?></td>
                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                <td><?php echo $post['category_name'] ?? 'دسته‌بندی نشده'; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $post['status']; ?>">
                                        <?php echo $post['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo jdate($post['created_at']); ?></td>
                                <td class="actions">
                                    <a href="/admin/posts/edit/<?php echo $post['id']; ?>" class="btn-edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/admin/posts/delete/<?php echo $post['id']; ?>" class="btn-delete" 
                                       onclick="return confirm('آیا از حذف این پست مطمئن هستید؟')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>