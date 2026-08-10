<div class="admin-container">
    
    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>👥 مدیریت کاربران</h1>
            <span>لیست تمام کاربران سیستم</span>
        </div>
        
        <!-- آمار کاربران -->
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h3><?php echo count($users); ?></h3>
                    <p>کل کاربران</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
                <div class="stat-info">
                    <h3><?php echo count(array_filter($users, fn($u) => $u['is_active'])); ?></h3>
                    <p>کاربران فعال</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="fas fa-user-shield"></i></div>
                <div class="stat-info">
                    <h3><?php echo count(array_filter($users, fn($u) => $u['role'] === 'admin')); ?></h3>
                    <p>مدیران</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-user-times"></i></div>
                <div class="stat-info">
                    <h3><?php echo count(array_filter($users, fn($u) => !$u['is_active'])); ?></h3>
                    <p>غیرفعال</p>
                </div>
            </div>
        </div>
        
        <!-- جدول کاربران -->
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>نام</th>
                        <th>ایمیل</th>
                        <th>نقش</th>
                        <th>وضعیت</th>
                        <th>ایمیل تأیید</th>
                        <th>آخرین ورود</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="8" style="text-align:center;">هیچ کاربری یافت نشد.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $index => $user): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                <?php if (!empty($user['company'])): ?>
                                    <br><small style="color: var(--gray);"><?php echo htmlspecialchars($user['company']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <?php 
                                $roleLabels = [
                                    'admin' => ['مدیر', 'published'],
                                    'editor' => ['ویراستار', 'unread'],
                                    'client' => ['مشتری', 'draft'],
                                    'user' => ['کاربر', 'read']
                                ];
                                $roleInfo = $roleLabels[$user['role']] ?? ['کاربر', 'read'];
                                ?>
                                <span class="status-badge <?php echo $roleInfo[1]; ?>">
                                    <?php echo $roleInfo[0]; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span class="status-badge published">فعال</span>
                                <?php else: ?>
                                    <span class="status-badge archived">غیرفعال</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['email_verified']): ?>
                                    <i class="fas fa-check-circle" style="color: #16A34A;"></i>
                                <?php else: ?>
                                    <i class="fas fa-times-circle" style="color: #DC2626;"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo $user['last_login'] ? date('Y/m/d H:i', strtotime($user['last_login'])) : '-'; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="/admin/users/edit/<?php echo $user['id']; ?>" class="btn-edit" title="ویرایش">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <button type="button" class="btn-delete" 
                                            onclick="if(confirm('آیا از حذف این کاربر اطمینان دارید؟')) window.location='/admin/users/delete/<?php echo $user['id']; ?>'"
                                            title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
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