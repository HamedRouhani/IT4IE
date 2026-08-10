<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar">
            <?php if (!empty($user['avatar'])): ?>
                <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="آواتار">
            <?php else: ?>
                <div class="avatar-placeholder">
                    <?php echo mb_substr($user['name'], 0, 1); ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="profile-info">
            <h1><?php echo htmlspecialchars($user['name']); ?></h1>
            <p class="email"><?php echo htmlspecialchars($user['email']); ?></p>
            <?php if (!empty($user['company'])): ?>
                <p class="company"><i class="fas fa-building"></i> <?php echo htmlspecialchars($user['company']); ?></p>
            <?php endif; ?>
            <div class="profile-badges">
                <span class="badge badge-<?php echo $user['role']; ?>">
                    <?php 
                    $roles = ['admin' => 'مدیر', 'editor' => 'ویراستار', 'client' => 'مشتری', 'user' => 'کاربر'];
                    echo $roles[$user['role']] ?? 'کاربر';
                    ?>
                </span>
                <?php if ($user['email_verified']): ?>
                    <span class="badge badge-success">
                        <i class="fas fa-check-circle"></i> ایمیل تأیید شده
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="profile-actions">
            <a href="/profile/edit" class="btn btn-primary">
                <i class="fas fa-edit"></i> ویرایش پروفایل
            </a>
        </div>
    </div>

    <div class="profile-content">
        <div class="profile-card">
            <h2><i class="fas fa-user"></i> اطلاعات شخصی</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>نام و نام خانوادگی</label>
                    <p><?php echo htmlspecialchars($user['name']); ?></p>
                </div>
                <div class="info-item">
                    <label>ایمیل</label>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <div class="info-item">
                    <label>شماره تماس</label>
                    <p><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'ثبت نشده'; ?></p>
                </div>
                <div class="info-item">
                    <label>شرکت/سازمان</label>
                    <p><?php echo !empty($user['company']) ? htmlspecialchars($user['company']) : 'ثبت نشده'; ?></p>
                </div>
                <div class="info-item">
                    <label>تاریخ عضویت</label>
                    <p><?php echo date('Y/m/d', strtotime($user['created_at'])); ?></p>
                </div>
                <div class="info-item">
                    <label>آخرین ورود</label>
                    <p><?php echo $user['last_login'] ? date('Y/m/d H:i', strtotime($user['last_login'])) : 'ثبت نشده'; ?></p>
                </div>
            </div>
        </div>

        <?php if (!empty($user['bio'])): ?>
        <div class="profile-card">
            <h2><i class="fas fa-info-circle"></i> درباره من</h2>
            <p class="bio-text"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>