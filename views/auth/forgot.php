<div class="auth-container">
    <div class="auth-box">
        <h1 class="auth-title">🔐 بازیابی رمز عبور</h1>
        
        <p class="auth-description">
            ایمیل خود را وارد کنید تا لینک بازیابی رمز عبور برای شما ارسال شود.
        </p>
        
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="/forgot-password" class="auth-form" id="forgotForm">
            <div class="form-group">
                <label for="email">📧 ایمیل</label>
                <div class="input-group">
                    <span class="input-icon">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" id="email" name="email" required 
                           placeholder="example@email.com" 
                           value="<?php echo $_POST['email'] ?? ''; ?>"
                           maxlength="100">
                </div>
            </div>
            
            <!-- Captcha -->
            <div class="form-group captcha-group">
                <label for="captcha">کد امنیتی</label>
                <div class="captcha-container">
                    <div class="captcha-image">
                        <img src="/refresh_captcha.php?<?php echo time(); ?>" 
                            alt="کد امنیتی" 
                            id="captcha-img">
                        <button type="button" class="refresh-captcha" title="بارگذاری مجدد">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="captcha-input">
                        <div class="input-group">
                            <span class="input-icon">
                                <i class="fas fa-shield-alt"></i>
                            </span>
                            <input type="text" id="captcha" name="captcha" required 
                                placeholder="۴ رقم" 
                                maxlength="4" 
                                autocomplete="off"
                                inputmode="numeric"
                                pattern="[0-9]*">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-options">
                <a href="/login" class="forgot-link">
                    <i class="fas fa-arrow-right"></i>
                    بازگشت به صفحه ورود
                </a>
            </div>
            
            <button type="submit" class="btn-auth" id="submitBtn">
                <i class="fas fa-paper-plane"></i>
                ارسال لینک بازیابی
            </button>
        </form>
        
        <div class="auth-footer">
            <p>حساب کاربری ندارید؟ <a href="/register">ثبت‌نام کنید</a></p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('forgotForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال ارسال...';
    });
});
</script>