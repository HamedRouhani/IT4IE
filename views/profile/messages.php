<div class="profile-container">
    <div class="profile-header">
        <div class="profile-info">
            <h1><i class="fas fa-comments"></i> پیام‌ها و پاسخ‌های من</h1>
            <p class="email">گفتگوهای شما با مدیریت IT4IE و پاسخ‌های دریافتی</p>
        </div>
        <div class="profile-actions">
            <a href="/contact" class="btn btn-primary">
                <i class="fas fa-plus"></i> پیام جدید
            </a>
            <a href="/profile" class="btn btn-primary">
                <i class="fas fa-user"></i> بازگشت به پروفایل
            </a>
        </div>
    </div>

    <div class="profile-content">
        <?php if (empty($messages)): ?>
            <div class="profile-card">
                <h2><i class="fas fa-inbox"></i> پیامی وجود ندارد</h2>
                <p class="bio-text">
                    هنوز گفتگویی با مدیریت نداشته‌اید. می‌توانید از صفحه «تماس با ما» پیام جدید ارسال کنید
                    یا چک‌لیست ارزیابی ریسک پروژه را تکمیل نمایید.
                </p>
            </div>
        <?php else: ?>
            <?php foreach ($messages as $msg): ?>
                <div class="profile-card">
                    <h2>
                        <i class="fas fa-envelope-open-text"></i>
                        <?php echo htmlspecialchars($msg['subject']); ?>
                    </h2>

                    <div class="message-item status-<?php echo $msg['status']; ?>" style="border: none; box-shadow: none;">
                        <div class="message-header">
                            <div class="message-subject">
                                <span class="message-status status-<?php echo $msg['status']; ?>">
                                    <?php if ($msg['status'] === 'replied'): ?>
                                        <i class="fas fa-check-circle"></i> پاسخ داده شده
                                    <?php elseif ($msg['status'] === 'read'): ?>
                                        <i class="fas fa-eye"></i> خوانده شده
                                    <?php else: ?>
                                        <i class="fas fa-clock"></i> در انتظار پاسخ
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <div class="message-body">
                            <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                            <span class="message-date">
                                <i class="fas fa-calendar-alt"></i>
                                <?php echo jdate($msg['created_at']); ?>
                            </span>
                        </div>

                        <?php if (!empty($msg['replies'])): ?>
                            <div class="message-replies">
                                <?php foreach ($msg['replies'] as $reply): ?>
                                    <div class="reply-item">
                                        <div class="reply-header">
                                            <i class="fas fa-reply"></i>
                                            <strong>پاسخ مدیر</strong>
                                            <span class="reply-date">
                                                <i class="fas fa-calendar-alt"></i>
                                                <?php echo jdate($reply['created_at']); ?>
                                            </span>
                                        </div>
                                        <p><?php echo nl2br(htmlspecialchars($reply['message'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="bio-text">هنوز پاسخی برای این گفتگو ثبت نشده است.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>