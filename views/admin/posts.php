<div class="admin-container">
    
    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>📝 مدیریت پست‌ها</h1>
            <a href="/admin/posts/create" class="btn-register" style="padding: 8px 16px; font-size: 14px; text-decoration: none;">
                <i class="fas fa-plus"></i> پست جدید
            </a>
        </div>
        
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-file-alt"></i></div>
                <div class="stat-info">
                    <h3><?php echo count($posts); ?></h3>
                    <p>کل مطالب</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <h3><?php echo count(array_filter($posts, fn($p) => $p['status'] === 'published')); ?></h3>
                    <p>منتشر شده</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="fas fa-edit"></i></div>
                <div class="stat-info">
                    <h3><?php echo count(array_filter($posts, fn($p) => $p['status'] === 'draft')); ?></h3>
                    <p>پیش‌نویس</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-archive"></i></div>
                <div class="stat-info">
                    <h3><?php echo count(array_filter($posts, fn($p) => $p['status'] === 'archived')); ?></h3>
                    <p>آرشیو</p>
                </div>
            </div>
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
                            <td colspan="6" style="text-align: center; color: var(--gray); padding: 30px;">
                                <i class="fas fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
                                هنوز پستی وجود ندارد.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($posts as $index => $post): 
                            // تبدیل وضعیت به فارسی
                            $statusLabels = [
                                'published' => ['منتشر شده', 'published'],
                                'draft' => ['پیش‌نویس', 'draft'],
                                'archived' => ['آرشیو', 'archived']
                            ];
                            $statusInfo = $statusLabels[$post['status']] ?? ['نامشخص', 'draft'];
                        ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                                    <?php if (!empty($post['summary'])): ?>
                                        <br><small style="color: var(--gray);"><?php echo htmlspecialchars(mb_substr(strip_tags($post['summary']), 0, 80)); ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($post['category_name'])): ?>
                                        <span class="status-badge published">
                                            <i class="fas fa-folder"></i> <?php echo htmlspecialchars($post['category_name']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge draft">دسته‌بندی نشده</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $statusInfo[1]; ?>">
                                        <?php echo $statusInfo[0]; ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y/m/d H:i', strtotime($post['created_at'])); ?></td>
                                <td class="actions">
                                    <a href="/post/<?php echo htmlspecialchars($post['slug']); ?>" class="btn-view" target="_blank" title="مشاهده در سایت">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/admin/posts/edit/<?php echo $post['id']; ?>" class="btn-edit" title="ویرایش">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn-delete" 
                                            onclick="if(confirm('آیا از حذف این پست مطمئن هستید؟')) window.location='/admin/posts/delete/<?php echo $post['id']; ?>'"
                                            title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>