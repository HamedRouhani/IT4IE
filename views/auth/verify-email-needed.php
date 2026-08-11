<div class="auth-container">
    <div class="auth-box" style="max-width: 550px; text-align: center;">
        
        <!-- آیکون اصلی -->
        <div style="font-size: 4rem; color: var(--primary); margin-bottom: 16px;">
            <i class="fas fa-envelope-open-text"></i>
        </div>
        
        <h1 class="auth-title">ایمیل خود را تأیید کنید</h1>
        
        <p style="color: var(--gray-dark); margin-bottom: 24px; line-height: 1.8;">
            یک <strong>لینک تأیید حساب کاربری</strong> به ایمیل شما ارسال شد.
            <br>
            برای تکمیل ثبت‌نام، لطفاً ایمیل خود را بررسی کرده و روی لینک داخل آن کلیک کنید.
        </p>

        <!-- باکس نمایش ایمیل -->
        <?php if (!empty($email)): ?>
        <div class="email-sent-box">
            <div style="font-size: 13px; color: var(--primary); font-weight: 600; margin-bottom: 8px;">
                <i class="fas fa-paper-plane"></i> ایمیل ارسال شد به:
            </div>
            <div style="font-size: 16px; color: var(--dark); font-weight: 700; word-break: break-all; direction: ltr;">
                <?= htmlspecialchars($email) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- پیام‌های موفقیت/خطا -->
        <?php if (!empty($_SESSION['resend_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['resend_message']) ?>
            </div>
            <?php unset($_SESSION['resend_message']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['resend_error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['resend_error']) ?>
            </div>
            <?php unset($_SESSION['resend_error']); ?>
        <?php endif; ?>

        <!-- باکس هشدار اسپم -->
        <div class="alert alert-warning-custom">
            <div style="display: flex; gap: 12px; align-items: flex-start; text-align: right;">
                <i class="fas fa-exclamation-triangle" style="color: #856404; font-size: 18px; flex-shrink: 0; margin-top: 2px;"></i>
                <div>
                    <strong style="display: block; margin-bottom: 8px;">ایمیل را پیدا نمی‌کنید؟</strong>
                    <ul style="margin: 0; padding-right: 20px; font-size: 13px; line-height: 1.8;">
                        <li>پوشه <strong>Spam</strong> یا <strong>Junk</strong> خود را چک کنید</li>
                        <li>در Gmail پوشه <strong>Promotions</strong> را بررسی کنید</li>
                        <li>در Yahoo پوشه <strong>Spam</strong> را حتماً ببینید</li>
                        <li>چند دقیقه صبر کنید و دوباره Inbox را رفرش کنید</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- فرم ارسال مجدد -->
        <form method="POST" action="/auth/resend-verification" style="margin-bottom: 16px;">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email ?? '') ?>">
            <button type="submit" class="btn-auth">
                <i class="fas fa-redo"></i>
                ارسال مجدد ایمیل تأیید
            </button>
        </form>

        <!-- فوتر باکس -->
        <div class="auth-footer" style="border-top: none; margin-top: 0; padding-top: 0;">
            <p style="font-size: 13px; color: var(--gray); margin-bottom: 8px;">
                <i class="fas fa-life-ring"></i>
                مشکل ادامه دارد؟ 
                <a href="/contact" style="color: var(--primary); font-weight: 600;">با ما تماس بگیرید</a>
            </p>
            <a href="/" style="color: var(--gray-dark); font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fas fa-arrow-right"></i>
                بازگشت به صفحه اصلی
            </a>
        </div>
    </div>
</div>