<div class="admin-container">
    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>📨 ارسال پیام درون‌برنامه‌ای</h1>
            <a href="/admin/checklist/view/<?php echo $submission['id']; ?>" class="btn-register" style="padding: 8px 16px; font-size: 14px; text-decoration: none;">
                <i class="fas fa-arrow-right"></i> بازگشت به جزئیات
            </a>
        </div>

        <div class="admin-form">
            <div style="background: rgba(108, 60, 225, 0.06); border-radius: var(--radius); padding: 12px 16px; margin-bottom: 16px; font-size: 0.85rem; color: var(--gray-dark);">
                <i class="fas fa-user" style="color: var(--primary); margin-left: 6px;"></i>
                گیرنده: <strong><?php echo htmlspecialchars($submission['name']); ?></strong>
                (<?php echo htmlspecialchars($submission['email']); ?>)
                — این پیام در بخش «پیام‌های قبلی شما» در صفحه «تماس با ما» کاربر نمایش داده می‌شود.
            </div>

            <form method="POST" action="/admin/checklist/message/<?php echo $submission['id']; ?>">
                <div class="form-group">
                    <label>موضوع پیام</label>
                    <input type="text" name="subject" required value="<?php echo htmlspecialchars($defaultSubject); ?>">
                </div>
                <div class="form-group">
                    <label>متن پیام</label>
                    <textarea name="message" required placeholder="مثال: سلام، بر اساس نتیجه ارزیابی چک‌لیست شما، پیشنهاد می‌کنیم..."></textarea>
                </div>
                <button type="submit" class="btn-admin-submit">
                    <i class="fas fa-paper-plane"></i> ارسال پیام
                </button>
            </form>
        </div>
    </div>
</div>