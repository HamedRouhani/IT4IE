<div class="admin-container">
    
    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>📝 ویرایش کاربر: <?php echo htmlspecialchars($user['name']); ?></h1>
            <a href="/admin/users" style="color: var(--primary); text-decoration: none;">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
        
        <div class="admin-form">
            <form method="POST" action="/admin/users/update">
                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                
                <div class="form-group">
                    <label>نام</label>
                    <input type="text" value="<?php echo htmlspecialchars($user['name']); ?>" disabled>
                    <small style="color: var(--gray);">نام قابل تغییر نیست</small>
                </div>
                
                <div class="form-group">
                    <label>ایمیل</label>
                    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    <small style="color: var(--gray);">ایمیل قابل تغییر نیست</small>
                </div>
                
                <div class="form-group">
                    <label for="role">نقش کاربری</label>
                    <select name="role" id="role">
                        <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>کاربر عادی</option>
                        <option value="client" <?php echo $user['role'] === 'client' ? 'selected' : ''; ?>>مشتری</option>
                        <option value="editor" <?php echo $user['role'] === 'editor' ? 'selected' : ''; ?>>ویراستار</option>
                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>مدیر</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" value="1" 
                               <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                        کاربر فعال باشد
                    </label>
                </div>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--gray-light);">
                    <h3 style="margin-bottom: 15px;">اطلاعات حساب</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div>
                            <small style="color: var(--gray);">تاریخ عضویت</small>
                            <p><strong><?php echo date('Y/m/d', strtotime($user['created_at'])); ?></strong></p>
                        </div>
                        <div>
                            <small style="color: var(--gray);">آخرین ورود</small>
                            <p><strong><?php echo $user['last_login'] ? date('Y/m/d H:i', strtotime($user['last_login'])) : '-'; ?></strong></p>
                        </div>
                        <div>
                            <small style="color: var(--gray);">ایمیل تأیید شده</small>
                            <p><strong><?php echo $user['email_verified'] ? 'بله' : 'خیر'; ?></strong></p>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 25px; display: flex; gap: 10px;">
                    <button type="submit" class="btn-admin-submit">
                        <i class="fas fa-save"></i> ذخیره تغییرات
                    </button>
                    <a href="/admin/users" style="padding: 10px 28px; background: var(--gray-light); color: var(--dark); border-radius: var(--radius-full); text-decoration: none;">
                        انصراف
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>