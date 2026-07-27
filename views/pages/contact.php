<div class="contact-page">
    <div class="contact-container">
        
        <!-- ============================================
             HEADER
             ============================================ -->
        <div class="contact-header">
            <h2 class="contact-title">📬تماس با <span>IT4IE</span></h2>
            <p class="contact-subtitle">
                ما همیشه آماده شنیدن نظرات، پیشنهادات و سوالات شما هستیم.
                فرم زیر را پر کنید تا در اسرع وقت با شما تماس بگیریم.
            </p>
        </div>
        
        <!-- ============================================
             هشدار عدم ورود
             ============================================ -->
        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="contact-alert contact-alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>برای ارسال پیام باید وارد حساب کاربری خود شوید.</strong>
                    <br>
                    <a href="/login">وارد شوید</a>
                    یا
                    <a href="/register">ثبت‌نام کنید</a>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- ============================================
             ALERTS
             ============================================ -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="contact-alert contact-alert-success">
                <i class="fas fa-check-circle"></i>
                <div><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="contact-alert contact-alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- ============================================
             FORM
             ============================================ -->
        <div class="contact-form-wrapper">
            <div class="contact-form-card">
                <h3 class="form-title">
                    <i class="fas fa-pen-fancy"></i>
                    ارسال پیام جدید
                </h3>
                
                <form method="POST" action="/contact" class="contact-form" id="contactForm" novalidate>
                    <!-- ردیف اول: نام و ایمیل -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">
                                <i class="fas fa-user-circle"></i>
                                نام و نام خانوادگی
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="name" name="name" required 
                                       placeholder="نام کامل خود را وارد کنید"
                                       value="<?php echo $_SESSION['user_name'] ?? ''; ?>">
                                <span class="input-focus-border"></span>
                                <span class="input-error" id="name-error"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i>
                                آدرس ایمیل
                            </label>
                            <div class="input-wrapper">
                                <input type="email" id="email" name="email" required 
                                       placeholder="example@email.com"
                                       value="<?php echo $_SESSION['user_email'] ?? ''; ?>">
                                <span class="input-focus-border"></span>
                                <span class="input-error" id="email-error"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ردیف دوم: تلفن و موضوع -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">
                                <i class="fas fa-phone-alt"></i>
                                شماره تماس
                                <span class="optional">(اختیاری)</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="phone" name="phone" 
                                       placeholder="۰۹۱۲۳۴۵۶۷۸۹">
                                <span class="input-focus-border"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="subject">
                                <i class="fas fa-tag"></i>
                                موضوع پیام
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="subject" name="subject" required 
                                       placeholder="موضوع پیام خود را وارد کنید">
                                <span class="input-focus-border"></span>
                                <span class="input-error" id="subject-error"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ردیف سوم: پیام -->
                    <div class="form-group full-width">
                        <label for="message">
                            <i class="fas fa-comment-dots"></i>
                            متن پیام
                        </label>
                        <div class="input-wrapper">
                            <textarea id="message" name="message" rows="6" required 
                                      placeholder="پیام خود را به طور کامل وارد کنید..."></textarea>
                            <span class="input-focus-border"></span>
                            <span class="input-error" id="message-error"></span>
                        </div>
                        <div class="char-counter">
                            <span id="charCount">0</span> / <span id="charMax">۵۰۰</span> کاراکتر
                        </div>
                    </div>
                    
                    <!-- دکمه ارسال -->
                    <button type="submit" class="btn-submit" id="submitBtn" 
                            <?php echo !isset($_SESSION['user_id']) ? 'disabled style="opacity:0.6; cursor:not-allowed;"' : ''; ?>>
                        <i class="fas fa-paper-plane"></i>
                        <span><?php echo isset($_SESSION['user_id']) ? 'ارسال پیام' : 'برای ارسال پیام وارد شوید'; ?></span>
                    </button>
                    
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <div style="text-align: center; margin-top: 12px; font-size: 14px; color: #94a3b8;">
                            <a href="/login" style="color: #2563eb; font-weight: 500;">وارد شوید</a>
                            یا
                            <a href="/register" style="color: #2563eb; font-weight: 500;">ثبت‌نام کنید</a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- ============================================
             USER MESSAGES
             ============================================ -->
        <?php if (isset($_SESSION['user_id']) && !empty($userMessages)): ?>
            <div class="user-messages-section">
                <h2 class="messages-title">
                    <i class="fas fa-history"></i>
                    پیام‌های قبلی شما
                    <span class="messages-count"><?php echo count($userMessages); ?></span>
                </h2>
                
                <div class="messages-list">
                    <?php foreach ($userMessages as $msg): ?>
                        <div class="message-item <?php echo $msg['status'] === 'unread' ? 'message-unread' : ''; ?>">
                            <div class="message-header">
                                <div class="message-subject">
                                    <i class="fas fa-envelope-open-text"></i>
                                    <span><?php echo htmlspecialchars($msg['subject']); ?></span>
                                </div>
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
                            
                            <div class="message-body">
                                <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                <span class="message-date">
                                    <i class="fas fa-calendar-alt"></i>
                                    <?php echo jdate($msg['created_at']); ?>
                                </span>
                            </div>
                            
                            <?php 
                            $messageModel = new \App\Models\Message();
                            $replies = $messageModel->getReplies($msg['id']);
                            if (!empty($replies)): 
                            ?>
                                <div class="message-replies">
                                    <?php foreach ($replies as $reply): ?>
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
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<!-- ============================================
     SCRIPT
     ============================================ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    const submitBtn = document.getElementById('submitBtn');
    const messageField = document.getElementById('message');
    const charCount = document.getElementById('charCount');
    const charMax = document.getElementById('charMax');
    const maxLength = 500;
    
    // ============================================
    // Character Counter
    // ============================================
    if (messageField) {
        charMax.textContent = maxLength;
        
        messageField.addEventListener('input', function() {
            const currentLength = this.value.length;
            charCount.textContent = currentLength;
            
            if (currentLength > maxLength) {
                this.value = this.value.substring(0, maxLength);
                charCount.textContent = maxLength;
            }
            
            if (currentLength > maxLength * 0.8) {
                charCount.style.color = '#f59e0b';
            } else {
                charCount.style.color = '#94a3b8';
            }
        });
    }
    
    // ============================================
    // Client-side Validation
    // ============================================
    if (form) {
        form.addEventListener('submit', function(e) {
            // اگر کاربر لاگین نبود، ارسال نشود
            <?php if (!isset($_SESSION['user_id'])): ?>
                e.preventDefault();
                alert('برای ارسال پیام لطفاً ابتدا وارد حساب کاربری خود شوید.');
                window.location.href = '/login';
                return false;
            <?php endif; ?>
            
            let isValid = true;
            const fields = [
                { id: 'name', errorId: 'name-error', message: 'لطفاً نام خود را وارد کنید.' },
                { id: 'email', errorId: 'email-error', message: 'لطفاً ایمیل معتبر وارد کنید.' },
                { id: 'subject', errorId: 'subject-error', message: 'لطفاً موضوع پیام را وارد کنید.' },
                { id: 'message', errorId: 'message-error', message: 'لطفاً متن پیام را وارد کنید.' }
            ];
            
            fields.forEach(function(field) {
                const input = document.getElementById(field.id);
                const error = document.getElementById(field.errorId);
                if (input && error) {
                    if (!input.value.trim()) {
                        input.classList.add('error');
                        error.textContent = field.message;
                        isValid = false;
                    } else {
                        input.classList.remove('error');
                        error.textContent = '';
                    }
                }
            });
            
            // بررسی ایمیل
            const emailInput = document.getElementById('email');
            const emailError = document.getElementById('email-error');
            if (emailInput && emailInput.value.trim()) {
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(emailInput.value.trim())) {
                    emailInput.classList.add('error');
                    emailError.textContent = 'لطفاً یک ایمیل معتبر وارد کنید.';
                    isValid = false;
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                const firstError = document.querySelector('.input-wrapper input.error, .input-wrapper textarea.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
                return false;
            }
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>در حال ارسال...</span>';
        });
    }
});
</script>