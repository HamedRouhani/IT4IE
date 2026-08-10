<div class="admin-container">
    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>📁 مدیریت دسته‌بندی‌ها</h1>
            <a href="/admin/categories/create" style="background: var(--primary); color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
                <i class="fas fa-plus"></i> دسته‌بندی جدید
            </a>
        </div>
        
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-layer-group"></i></div>
                <div class="stat-info">
                    <h3><?php echo count($categories); ?></h3>
                    <p>کل دسته‌ها</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-sitemap"></i></div>
                <div class="stat-info">
                    <h3><?php echo count(array_filter($categories, fn($c) => empty($c['parent_id']))); ?></h3>
                    <p>دسته‌های اصلی</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="fas fa-folder-tree"></i></div>
                <div class="stat-info">
                    <h3><?php echo count(array_filter($categories, fn($c) => !empty($c['parent_id']))); ?></h3>
                    <p>زیردسته‌ها</p>
                </div>
            </div>
        </div>
        
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>آیکون</th>
                        <th>نام</th>
                        <th>اسلاگ</th>
                        <th>والد</th>
                        <th>تعداد مطالب</th>
                        <th>زیردسته</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr><td colspan="8" style="text-align:center; padding: 30px;">هیچ دسته‌بندی‌ای وجود ندارد.</td></tr>
                    <?php else: ?>
                        <?php foreach ($categories as $index => $cat): 
                            // پیدا کردن نام والد
                            $parentName = '-';
                            if (!empty($cat['parent_id'])) {
                                foreach ($categories as $p) {
                                    if ($p['id'] == $cat['parent_id']) {
                                        $parentName = $p['name'];
                                        break;
                                    }
                                }
                            }
                        ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><i class="<?php echo htmlspecialchars($cat['icon'] ?? 'fas fa-folder'); ?>" style="color: var(--primary); font-size: 1.2rem;"></i></td>
                            <td>
                                <strong><?php echo htmlspecialchars($cat['name']); ?></strong>
                                <?php if (!empty($cat['description'])): ?>
                                    <br><small style="color: var(--gray);"><?php echo htmlspecialchars(mb_substr($cat['description'], 0, 60)); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><code><?php echo htmlspecialchars($cat['slug']); ?></code></td>
                            <td><?php echo htmlspecialchars($parentName); ?></td>
                            <td>
                                <span class="status-badge published"><?php echo $cat['post_count'] ?? 0; ?> مطلب</span>
                            </td>
                            <td>
                                <span class="status-badge unread"><?php echo $cat['child_count'] ?? 0; ?> زیردسته</span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="/category/<?php echo htmlspecialchars($cat['slug']); ?>" class="btn-view" target="_blank" title="مشاهده در سایت">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/admin/categories/edit/<?php echo $cat['id']; ?>" class="btn-edit" title="ویرایش">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn-delete" 
                                            onclick="if(confirm('آیا از حذف این دسته‌بندی اطمینان دارید؟ این عملیات قابل بازگشت نیست.')) window.location='/admin/categories/delete/<?php echo $cat['id']; ?>'"
                                            title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>