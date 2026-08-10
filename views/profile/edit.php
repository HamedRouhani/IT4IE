<div class="profile-edit-container">
    <div class="profile-edit-header">
        <h1><i class="fas fa-edit"></i> ویرایش پروفایل</h1>
        <a href="/profile" class="btn btn-secondary">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <div class="profile-edit-content">
        <!-- فرم اطلاعات شخصی -->
        <div class="edit-card">
            <h2><i class="fas fa-user"></i> اطلاعات شخصی</h2>
            <form method="POST" action="/profile/update" enctype="multipart/form-data">
                <div class="form-group">
                    <label>تصویر پروفایل</label>
                    <div class="avatar-upload">
                        <div class="current-avatar">
                            <?php if (!empty($user['avatar'])): ?>
                                <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="آواتار فعلی">
                            <?php else: ?>
                                <div class="avatar-placeholder">
                                    <?php echo mb_substr($user['name'], 0, 1); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="upload-controls">
                            <input type="file" name="avatar" id="avatar" accept="image/*" class="form-control">
                            <small class="form-text">حداکثر ۲ مگابایت - فرمت‌های JPG, PNG, GIF, WEBP</small>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">نام و نام خانوادگی *</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">شماره تماس</label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="company">شرکت/سازمان</label>
                    <input type="text" id="company" name="company" class="form-control" 
                           value="<?php echo htmlspecialchars($user['company'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="bio">درباره من</label>
                    <textarea id="bio" name="bio" class="form-control" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> ذخیره تغییرات
                    </button>
                    <a href="/profile" class="btn btn-secondary">انصراف</a>
                </div>
            </form>
        </div>

        <!-- فرم تغییر رمز عبور -->
        <div class="edit-card">
            <h2><i class="fas fa-lock"></i> تغییر رمز عبور</h2>
            <form method="POST" action="/profile/password">
                <div class="form-group">
                    <label for="current_password">رمز عبور فعلی *</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="new_password">رمز عبور جدید *</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" 
                               minlength="6" required>
                        <small class="form-text">حداقل ۶ کاراکتر</small>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">تکرار رمز عبور جدید *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key"></i> تغییر رمز عبور
                    </button>
                </div>
            </form>
        </div>

        <!-- اطلاعات حساب -->
        <div class="edit-card">
            <h2><i class="fas fa-info-circle"></i> اطلاعات حساب</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>ایمیل</label>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                    <small class="text-muted">ایمیل قابل تغییر نیست</small>
                </div>
                <div class="info-item">
                    <label>نقش کاربری</label>
                    <p>
                        <?php 
                        $roles = ['admin' => 'مدیر', 'editor' => 'ویراستار', 'client' => 'مشتری', 'user' => 'کاربر'];
                        echo $roles[$user['role']] ?? 'کاربر';
                        ?>
                    </p>
                </div>
                <div class="info-item">
                    <label>تاریخ عضویت</label>
                    <p><?php echo date('Y/m/d', strtotime($user['created_at'])); ?></p>
                </div>
                <div class="info-item">
                    <label>آخرین تغییر رمز عبور</label>
                    <p><?php echo $user['last_password_change'] ? date('Y/m/d', strtotime($user['last_password_change'])) : 'ثبت نشده'; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>